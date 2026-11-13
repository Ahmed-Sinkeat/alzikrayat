// End-to-end API check. Run against a THROWAWAY database only:
//   TEST_MONGODB_URI=mongodb://127.0.0.1:27017/alzikrayat_test npm run selftest
// It drops that database when it finishes.
require('dotenv').config();
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const mongoose = require('mongoose');

process.env.JWT_SECRET = process.env.JWT_SECRET || 'selftest-secret';
const uri = process.env.TEST_MONGODB_URI;
if (!uri) { console.error('Set TEST_MONGODB_URI to a throwaway database.'); process.exit(2); }

const app = require('./app');
const { UPLOAD_DIR } = require('./middleware/upload');

const PNG = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', 'base64');

async function main() {
  await mongoose.connect(uri);
  await mongoose.connection.dropDatabase();
  const server = app.listen(0);
  const base = `http://127.0.0.1:${server.address().port}/api`;
  const call = async (method, url, { token, json, form } = {}) => {
    const headers = {};
    if (token) headers.Authorization = `Bearer ${token}`;
    let body;
    if (json) { headers['Content-Type'] = 'application/json'; body = JSON.stringify(json); }
    if (form) body = form;
    const res = await fetch(base + url, { method, headers, body });
    return { status: res.status, body: await res.json() };
  };
  const files = () => fs.readdirSync(UPLOAD_DIR).filter((f) => f !== '.gitkeep');
  const photoForm = (title, mime = 'image/png', data = PNG) => {
    const f = new FormData();
    if (title !== undefined) f.append('title', title);
    f.append('description', 'desc');
    f.append('image', new Blob([data], { type: mime }), 'x.png');
    return f;
  };
  const before = files().length;

  // --- auth
  const u1 = { firstName: 'Ann', lastName: 'A', email: 'Ann@Test.com', password: 'secret1' };
  let r = await call('POST', '/auth/register', { json: u1 });
  assert.strictEqual(r.status, 201);
  assert.ok(r.body.token);
  assert.ok(!('password' in r.body.user), 'password hash leaked');
  const t1 = r.body.token;
  const stored = await mongoose.connection.collection('users').findOne({});
  assert.ok(stored.password.startsWith('$2'), 'password not bcrypt-hashed');
  assert.strictEqual(stored.email, 'ann@test.com');

  assert.strictEqual((await call('POST', '/auth/register', { json: u1 })).status, 409);
  assert.strictEqual((await call('POST', '/auth/register', { json: { ...u1, email: 'bad', password: '1' } })).status, 400);
  assert.strictEqual((await call('POST', '/auth/login', { json: { email: 'ann@test.com', password: 'wrong' } })).status, 401);
  assert.strictEqual((await call('POST', '/auth/login', { json: { email: 'nobody@test.com', password: 'secret1' } })).status, 401);
  r = await call('POST', '/auth/login', { json: { email: 'ANN@test.com', password: 'secret1' } });
  assert.strictEqual(r.status, 200);

  r = await call('POST', '/auth/register', { json: { firstName: 'Bob', lastName: 'B', email: 'bob@test.com', password: 'secret2' } });
  const t2 = r.body.token;

  // --- photos
  assert.strictEqual((await call('POST', '/photos', { form: photoForm('hi') })).status, 401);
  assert.strictEqual((await call('POST', '/photos', { token: t1, form: photoForm('hi', 'text/plain', Buffer.from('x')) })).status, 400);
  assert.strictEqual((await call('POST', '/photos', { token: t1, form: photoForm(undefined) })).status, 400);
  assert.strictEqual(files().length, before, 'rejected uploads left files on disk');

  r = await call('POST', '/photos', { token: t1, form: photoForm('First') });
  assert.strictEqual(r.status, 201);
  const photo = r.body.photo;
  assert.strictEqual(photo.userId.firstName, 'Ann');
  assert.ok(fs.existsSync(path.join(UPLOAD_DIR, photo.fileName)));

  r = await call('GET', '/photos');
  assert.strictEqual(r.body.photos.length, 1);
  assert.strictEqual((await call('GET', '/photos/not-an-id')).status, 400);
  assert.strictEqual((await call('GET', '/photos/' + new mongoose.Types.ObjectId())).status, 404);
  assert.strictEqual((await call('GET', '/nope')).status, 404);

  // --- comments
  assert.strictEqual((await call('POST', `/photos/${photo._id}/comments`, { json: { comment: 'x' } })).status, 401);
  assert.strictEqual((await call('POST', `/photos/${photo._id}/comments`, { token: t2, json: { comment: '  ' } })).status, 400);
  assert.strictEqual((await call('POST', `/photos/${new mongoose.Types.ObjectId()}/comments`, { token: t2, json: { comment: 'x' } })).status, 404);
  r = await call('POST', `/photos/${photo._id}/comments`, { token: t2, json: { comment: 'Nice!' } });
  assert.strictEqual(r.status, 201);
  r = await call('GET', `/photos/${photo._id}`);
  assert.strictEqual(r.body.comments.length, 1);
  assert.strictEqual(r.body.comments[0].userId.firstName, 'Bob');

  // --- delete ownership
  assert.strictEqual((await call('DELETE', `/photos/${photo._id}`)).status, 401);
  assert.strictEqual((await call('DELETE', `/photos/${photo._id}`, { token: t2 })).status, 403);
  assert.strictEqual((await call('DELETE', `/photos/${photo._id}`, { token: t1 })).status, 200);
  assert.strictEqual((await call('GET', `/photos/${photo._id}`)).status, 404);
  assert.strictEqual(await mongoose.connection.collection('comments').countDocuments(), 0, 'comments not cascaded');
  await new Promise((ok) => setTimeout(ok, 100));
  assert.strictEqual(files().length, before, 'file not removed on delete');

  await mongoose.connection.dropDatabase();
  await mongoose.disconnect();
  server.close();
  console.log('selftest OK');
}

main().catch(async (err) => {
  console.error('selftest FAILED:', err);
  try { await mongoose.connection.dropDatabase(); } catch (e) { /* ignore */ }
  process.exit(1);
});
