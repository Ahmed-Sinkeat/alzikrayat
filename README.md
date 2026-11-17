# Alzikrayat

Photo sharing web app for the Advanced Web Technologies course (Project 3, MERN edition). Users register and log in, upload photos, browse a gallery, comment on photos, and delete their own photos.

| Layer | Tech |
|---|---|
| Front end | React 19, React Router, axios, Vite (`/client`) |
| API | Node.js, Express 5, Joi validation, multer uploads, JWT (`/server`) |
| Database | MongoDB (Atlas) through Mongoose |

Everything is hand-written: no Next.js, NestJS, Prisma or admin dashboards.

## Setup

Needs Node.js 20+ and a MongoDB database (a free MongoDB Atlas cluster works).

```bash
# 1. API
cd server
npm install
cp .env.example .env        # then fill in the values below
npm run dev                 # http://localhost:5000

# 2. Client (second terminal)
cd client
npm install
npm run dev                 # http://localhost:5173
```

The Vite dev server proxies `/api` and `/uploads` to the Express API, so no client config is needed.

### Environment variables (`server/.env`, never committed)

| Variable | Meaning |
|---|---|
| `MONGODB_URI` | MongoDB connection string (Atlas: Connect > Drivers) |
| `PORT` | API port, default `5000` |
| `JWT_SECRET` | Long random string used to sign login tokens |
| `JWT_EXPIRES_IN` | Token lifetime, default `7d` |
| `CLIENT_ORIGIN` | Allowed CORS origin, default `http://localhost:5173` |

## REST API

| Method | Route | Auth | Result |
|---|---|---|---|
| POST | `/api/auth/register` | no | 201 `{ token, user }` |
| POST | `/api/auth/login` | no | 200 `{ token, user }` |
| GET | `/api/photos` | no | all photos, newest first |
| GET | `/api/photos/:id` | no | one photo + its comments |
| POST | `/api/photos` | yes | multipart (`image`, `title`, `description`), 201 |
| DELETE | `/api/photos/:id` | owner | removes the photo, its file and its comments |
| POST | `/api/photos/:id/comments` | yes | 201 `{ comment }` |

Send the token as `Authorization: Bearer <token>`. Errors are JSON `{ success: false, message }` with 400 (validation), 401 (not logged in), 403 (not the owner), 404 or 409 (email already used).

## Data model

- **User**: firstName, lastName, email (unique), password (bcrypt hash), bio
- **Photo**: userId -> User, fileName, title, description, dateTime
- **Comment**: photoId -> Photo, userId -> User, comment, dateTime

## Check

`server/selftest.js` runs the whole API (register, login, upload, comment, ownership on delete) against a database. Use a throwaway one, because it drops that database when done:

```bash
cd server
TEST_MONGODB_URI=mongodb://127.0.0.1:27017/alzikrayat_test npm run selftest
```

## Project layout

```
server/   models/ routes/ controllers/ middleware/ uploads/ app.js server.js
client/   src/components/ src/pages/ src/context/ src/App.jsx src/main.jsx vite.config.js
```

---

## Project 1 version (PHP + MySQL)

The earlier hand-written PHP MVC version is still in the root folders (`config`, `controllers`, `core`, `database`, `models`, `public`, `views`, `tests`).

Photo sharing web app for the web programming course (Project 1). Users sign up, upload photos with a short story, browse the gallery, comment, and delete their own photos.

Plain PHP + MySQL, no framework. Handwritten MVC with a small regex router.

### Quick run (no Apache)

Needs PHP 8 and MySQL/MariaDB running.

```bash
mysql -u root < database/schema.sql
php -S localhost:8000 -t public public/index.php
```

Then open `http://localhost:8000`.

### Setup (XAMPP)

1. Copy the folder into `htdocs` (e.g. `C:\xampp\htdocs\alzikrayat`).
2. Start Apache and MySQL.
3. Import `database/schema.sql` in phpMyAdmin. It creates the `alzikrayat` database.
4. Make sure `mod_rewrite` is on and `AllowOverride All` is set for `htdocs`.
5. Open `http://localhost/alzikrayat/public/`.

The default DB login is `root` with no password (`config/database.php`). You can override it with Apache `SetEnv DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS` (or `DB_SOCKET`).

On Linux, `public/images/uploads/` must be writable by Apache.

### Routes

| Method | Route | |
| --- | --- | --- |
| GET | `/` | home |
| GET | `/about` | about |
| GET/POST | `/register`, `/login` | auth |
| POST | `/logout` | logout |
| GET | `/photos` | gallery |
| GET | `/photos/create` | upload form |
| POST | `/photo/store` | save upload |
| GET | `/photo/{id}` | photo + comments |
| POST | `/photo/{id}/comments` | add comment |
| POST | `/photo/{id}/delete` | delete own photo |

### Notes

- Validation happens in three places: HTML5 attributes, `public/js/validation.js`, and again in PHP.
- Passwords use `password_hash`, all queries use prepared statements, forms have CSRF tokens.
- Uploads: JPEG/PNG/GIF/WebP up to 5 MB, checked by MIME type and saved with a random name.
- Extra feature: "Memory Mood" on the photo page switches between original, warm, and monochrome filters (CSS only, the file isn't changed).

### Tests

```bash
php tests/router_test.php
php tests/security_test.php
php tests/mysql_integration_test.php   # needs the database
```

The architecture report is in `docs/architecture-report.pdf`.

Name: Ahmed Mohammed Omer
