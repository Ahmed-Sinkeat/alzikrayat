// 404 for unknown API routes
function notFound(req, res) {
  res.status(404).json({ success: false, message: `Route not found: ${req.method} ${req.originalUrl}` });
}

// Central error handler: always answers with JSON
// eslint-disable-next-line no-unused-vars
function errorHandler(err, req, res, next) {
  let status = err.status || 500;
  let message = err.message || 'Server error';

  if (err.name === 'ValidationError' && err.errors) {
    status = 400;
    message = Object.values(err.errors).map((e) => e.message).join(', ');
  } else if (err.name === 'CastError') {
    status = 400;
    message = 'Invalid id';
  } else if (err.code === 11000) {
    status = 409;
    message = 'Duplicate value';
  } else if (err.name === 'MulterError') {
    status = 400;
  }

  if (status >= 500) console.error(err);
  res.status(status).json({ success: false, message });
}

module.exports = { notFound, errorHandler };
