const mongoose = require('mongoose');

const commentSchema = new mongoose.Schema({
  photoId: { type: mongoose.Schema.Types.ObjectId, ref: 'Photo', required: true, index: true },
  userId: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  comment: { type: String, required: [true, 'Comment is required'], trim: true, maxlength: 500 },
  dateTime: { type: Date, default: Date.now },
});

module.exports = mongoose.model('Comment', commentSchema);
