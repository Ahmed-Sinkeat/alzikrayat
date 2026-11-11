const express = require('express');
const { register, login } = require('../controllers/authController');
const { validate } = require('../middleware/validation');

const router = express.Router();

router.post('/register', validate('register'), register);
router.post('/login', validate('login'), login);

module.exports = router;
