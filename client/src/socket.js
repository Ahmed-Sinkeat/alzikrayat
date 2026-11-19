import { io } from 'socket.io-client';

// One shared connection for the whole app. Same origin: in development Vite proxies
// /socket.io to the Express server (see vite.config.js).
const socket = io();

export default socket;
