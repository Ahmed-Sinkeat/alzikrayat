import axios from 'axios';

const api = axios.create({ baseURL: '/api' });

// attach the JWT to every request when logged in
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// readable message from an axios error
export function errorMessage(err) {
  return err.response?.data?.message || err.message || 'Something went wrong';
}

export const imageUrl = (fileName) => `/uploads/${fileName}`;

export default api;
