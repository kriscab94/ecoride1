import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8080/api';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle auth errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export const authService = {
  register: (userData) => api.post('/auth/register', userData),
  login: (credentials) => api.post('/auth/login', credentials),
  verifyEmail: (token) => api.post('/auth/verify-email', { token }),
  forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
  resetPassword: (token, password) => api.post('/auth/reset-password', { token, password }),
};

export const userService = {
  getProfile: () => api.get('/user/profile'),
  updateProfile: (data) => api.put('/user/profile', data),
  getCredits: () => api.get('/user/credits'),
};

export const vehicleService = {
  getVehicles: () => api.get('/vehicles'),
  createVehicle: (data) => api.post('/vehicles', data),
  updateVehicle: (id, data) => api.put(`/vehicles/${id}`, data),
  deleteVehicle: (id) => api.delete(`/vehicles/${id}`),
};

export const tripService = {
  searchTrips: (params) => api.get('/trips', { params }),
  createTrip: (data) => api.post('/trips', data),
  getTripById: (id) => api.get(`/trips/${id}`),
  updateTrip: (id, data) => api.put(`/trips/${id}`, data),
  cancelTrip: (id) => api.delete(`/trips/${id}`),
  getDriverTrips: () => api.get('/trips/user/driver'),
  getPassengerTrips: () => api.get('/trips/user/passenger'),
};

export const participationService = {
  joinTrip: (tripId, data) => api.post(`/trips/${tripId}/participate`, data),
  updateParticipationStatus: (id, status) => api.put(`/participations/${id}/status`, { status }),
  getParticipation: (id) => api.get(`/participations/${id}`),
};

export const reviewService = {
  createReview: (tripId, data) => api.post(`/trips/${tripId}/reviews`, data),
  getUserReviews: (userId) => api.get(`/users/${userId}/reviews`),
};

export default api;