import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Ganti dengan IP server Laravel Anda
// export const BASE_URL = 'http://10.0.2.2:8000'; // Android emulator
export const BASE_URL = 'http://103.175.217.244:22180'; // Production server

const API_URL = `${BASE_URL}/api`;

const api = axios.create({
  baseURL: API_URL,
  timeout: 30000,
  headers: {
    Accept: 'application/json',
  },
});

// Request interceptor - attach JWT token
api.interceptors.request.use(
  async config => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  error => Promise.reject(error),
);

// Response interceptor - handle 401
api.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      await AsyncStorage.multiRemove(['auth_token', 'user_data']);
    }
    return Promise.reject(error);
  },
);

export default api;

// Auth
export const authService = {
  login: (email, password, fcmToken = null) =>
    api.post('/auth/login', {email, password, fcm_token: fcmToken}),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
  changePassword: (passwordLama, passwordBaru, passwordBaruConfirmation) =>
    api.post('/auth/change-password', {
      password_lama: passwordLama,
      password_baru: passwordBaru,
      password_baru_confirmation: passwordBaruConfirmation,
    }),
};

// Dashboard
export const dashboardService = {
  get: () => api.get('/dashboard'),
};

// Absensi
export const attendanceService = {
  getOffices: () => api.get('/attendance/offices'),
  getToday: () => api.get('/attendance/today'),
  getHistory: (bulan, tahun) =>
    api.get('/attendance/history', {params: {bulan, tahun}}),
  checkIn: formData => api.post('/attendance/check-in', formData),
  checkOut: formData => api.post('/attendance/check-out', formData),
};

// Izin
export const leaveService = {
  getAll: (page = 1) => api.get('/leave', {params: {page}}),
  getById: id => api.get(`/leave/${id}`),
  create: formData => api.post('/leave', formData),
};
