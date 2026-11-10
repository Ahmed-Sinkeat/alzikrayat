const mongoose = require('mongoose');

const userSchema = new mongoose.Schema({
  firstName: { type: String, required: [true, 'First name is required'], trim: true, maxlength: 50 },
  lastName: { type: String, required: [true, 'Last name is required'], trim: true, maxlength: 50 },
  email: {
    type: String,
    required: [true, 'Email is required'],
    unique: true,
    lowercase: true,
    trim: true,
    match: [/^\S+@\S+\.\S+$/, 'Email is invalid'],
  },
  password: { type: String, required: true, select: false }, // bcrypt hash
  bio: { type: String, trim: true, maxlength: 300, default: '' },
}, { timestamps: true });

// never leak the hash in JSON output
userSchema.set('toJSON', {
  transform: (doc, ret) => { delete ret.password; delete ret.__v; return ret; },
});

module.exports = mongoose.model('User', userSchema);
