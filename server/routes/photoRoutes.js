const express = require('express');
const { getAll, getOne, create, remove } = require('../controllers/photoController');
const { create: addComment } = require('../controllers/commentController');
const { requireAuth } = require('../middleware/auth');
const { upload } = require('../middleware/upload');
const { validate } = require('../middleware/validation');

const router = express.Router();

router.get('/', getAll);
router.get('/:id', getOne);
router.post('/', requireAuth, upload.single('image'), validate('photo'), create);
router.post('/:id/comments', requireAuth, validate('comment'), addComment);
router.delete('/:id', requireAuth, remove);

module.exports = router;
