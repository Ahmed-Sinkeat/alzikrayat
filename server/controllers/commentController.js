const mongoose = require('mongoose');
const Photo = require('../models/Photo');
const Comment = require('../models/Comment');
const { emitToPhoto } = require('../socket');

// POST /api/photos/:id/comments
async function create(req, res) {
  if (!mongoose.isValidObjectId(req.params.id)) {
    return res.status(400).json({ success: false, message: 'Invalid photo id' });
  }
  if (!(await Photo.exists({ _id: req.params.id }))) {
    return res.status(404).json({ success: false, message: 'Photo not found' });
  }

  const comment = await Comment.create({
    photoId: req.params.id,
    userId: req.userId,
    comment: req.body.comment,
  });
  await comment.populate('userId', 'firstName lastName');
  emitToPhoto(req.params.id, 'comment:new', comment); // live update for everyone on this photo's page
  res.status(201).json({ success: true, comment });
}

module.exports = { create };
