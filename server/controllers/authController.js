const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const User = require('../models/User');

const SALT_ROUNDS = 10;

function signToken(user) {
  return jwt.sign({ id: user._id }, process.env.JWT_SECRET, {
    expiresIn: process.env.JWT_EXPIRES_IN || '7d',
  });
}

// POST /api/auth/register
async function register(req, res) {
  const { firstName, lastName, email, password, bio } = req.body;

  if (await User.exists({ email: email.toLowerCase() })) {
    return res.status(409).json({ success: false, message: 'Email is already registered' });
  }

  const hash = await bcrypt.hash(password, SALT_ROUNDS);
  const user = await User.create({ firstName, lastName, email, password: hash, bio });

  res.status(201).json({ success: true, token: signToken(user), user });
}

// POST /api/auth/login
async function login(req, res) {
  const { email, password } = req.body;

  const user = await User.findOne({ email: email.toLowerCase() }).select('+password');
  const ok = user && (await bcrypt.compare(password, user.password));
  if (!ok) {
    // same message for both cases so emails can't be probed
    return res.status(401).json({ success: false, message: 'Invalid email or password' });
  }

  res.json({ success: true, token: signToken(user), user });
}

module.exports = { register, login };
