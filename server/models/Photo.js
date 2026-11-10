const mongoose = require('mongoose');

const photoSchema = new mongoose.Schema({
  userId: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true, index: true },
  fileName: { type: String, required: true },
  title: { type: String, required: [true, 'Title is required'], trim: true, maxlength: 100 },
  description: { type: String, trim: true, maxlength: 500, default: '' },
  dateTime: { type: Date, default: Date.now },
});

module.exports = mongoose.model('Photo', photoSchema);
