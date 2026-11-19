const { Server } = require('socket.io');
const mongoose = require('mongoose');

// One room per photo ("photo:<id>"): clients on a photo page join its room and get
// that photo's new comments pushed to them. Writing still goes through the REST API.
let io = null;

const room = (photoId) => `photo:${photoId}`;

function initSocket(httpServer) {
  io = new Server(httpServer, {
    cors: { origin: process.env.CLIENT_ORIGIN || 'http://localhost:5173' },
  });

  io.on('connection', (socket) => {
    socket.on('photo:join', (photoId) => {
      if (mongoose.isValidObjectId(photoId)) socket.join(room(photoId));
    });
    socket.on('photo:leave', (photoId) => {
      if (mongoose.isValidObjectId(photoId)) socket.leave(room(photoId));
    });
  });

  return io;
}

// Broadcast to everyone viewing a photo. No-op when sockets aren't running (e.g. tests using app only).
function emitToPhoto(photoId, event, data) {
  if (io) io.to(room(String(photoId))).emit(event, data);
}

module.exports = { initSocket, emitToPhoto };
