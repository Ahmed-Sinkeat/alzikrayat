const fs = require('fs');
const path = require('path');
const mongoose = require('mongoose');
const Photo = require('../models/Photo');
const Comment = require('../models/Comment');
const { UPLOAD_DIR } = require('../middleware/upload');

const OWNER_FIELDS = 'firstName lastName';

function badId(res) {
  return res.status(400).json({ success: false, message: 'Invalid photo id' });
}

// GET /api/photos
async function getAll(req, res) {
  const photos = await Photo.find().sort({ dateTime: -1 }).populate('userId', OWNER_FIELDS);
  res.json({ success: true, photos });
}

// GET /api/photos/:id
async function getOne(req, res) {
  if (!mongoose.isValidObjectId(req.params.id)) return badId(res);
  const photo = await Photo.findById(req.params.id).populate('userId', OWNER_FIELDS);
  if (!photo) return res.status(404).json({ success: false, message: 'Photo not found' });
  const comments = await Comment.find({ photoId: photo._id })
    .sort({ dateTime: 1 })
    .populate('userId', OWNER_FIELDS);
  res.json({ success: true, photo, comments });
}

// POST /api/photos  (multipart: image + title + description)
async function create(req, res) {
  if (!req.file) {
    return res.status(400).json({ success: false, message: 'An image file is required' });
  }
  try {
    const photo = await Photo.create({
      userId: req.userId,
      fileName: req.file.filename,
      title: req.body.title,
      description: req.body.description,
    });
    await photo.populate('userId', OWNER_FIELDS);
    res.status(201).json({ success: true, photo });
  } catch (err) {
    fs.unlink(req.file.path, () => {}); // DB write failed: don't leave an orphan file
    throw err;
  }
}

// DELETE /api/photos/:id  (owner only)
async function remove(req, res) {
  if (!mongoose.isValidObjectId(req.params.id)) return badId(res);
  const photo = await Photo.findById(req.params.id);
  if (!photo) return res.status(404).json({ success: false, message: 'Photo not found' });
  if (photo.userId.toString() !== req.userId) {
    return res.status(403).json({ success: false, message: 'You can only delete your own photos' });
  }

  await Comment.deleteMany({ photoId: photo._id });
  await photo.deleteOne();
  fs.unlink(path.join(UPLOAD_DIR, photo.fileName), () => {});
  res.json({ success: true, message: 'Photo deleted' });
}

module.exports = { getAll, getOne, create, remove };
