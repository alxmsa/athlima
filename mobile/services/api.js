import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// ── URL de base de l'API ──────────────────────────────────────────────────────
// En développement local avec Expo
const BASE_URL = 'http://localhost:8080/api';

// ── Instance Axios ────────────────────────────────────────────────────────────
const api = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  timeout: 10000,
});

// ── Intercepteur requête — ajoute le token JWT automatiquement ────────────────
api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('jwt_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// ── Intercepteur réponse — gère les erreurs globalement ──────────────────────
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expiré — supprimer le token
      await AsyncStorage.removeItem('jwt_token');
    }
    return Promise.reject(error);
  }
);

// ── Auth ──────────────────────────────────────────────────────────────────────
export const authService = {
  register: (data) => api.post('/register', data),
  login: async (email, password) => {
    const response = await api.post('/login_check', {
      username: email,
      password,
    });
    // Stocker le token JWT
    await AsyncStorage.setItem('jwt_token', response.data.token);
    return response;
  },
  logout: async () => {
    await AsyncStorage.removeItem('jwt_token');
  },
  getToken: () => AsyncStorage.getItem('jwt_token'),
};

// ── Profil ────────────────────────────────────────────────────────────────────
export const profilService = {
  get: ()       => api.get('/profil'),
  update: (data) => api.put('/profil', data),
  delete: ()    => api.delete('/profil'),
};

// ── Séances ───────────────────────────────────────────────────────────────────
export const seanceService = {
  getAll: ()        => api.get('/seances'),
  getOne: (id)      => api.get(`/seances/${id}`),
  create: (data)    => api.post('/seances', data),
  terminer: (id)    => api.patch(`/seances/${id}/terminer`),
  delete: (id)      => api.delete(`/seances/${id}`),
  getExercices: (id) => api.get(`/seances/${id}/exercices`),
  addExercice: (id, data) => api.post(`/seances/${id}/exercices`, data),
};

// ── Exercices ─────────────────────────────────────────────────────────────────
export const exerciceService = {
  getAll: (params) => api.get('/exercices', { params }),
  getOne: (id)     => api.get(`/exercices/${id}`),
  create: (data)   => api.post('/exercices', data),
  delete: (id)     => api.delete(`/exercices/${id}`),
};

// ── Séries ────────────────────────────────────────────────────────────────────
export const serieService = {
  getAll: (exerciceSeanceId) =>
    api.get(`/exercice-seances/${exerciceSeanceId}/series`),
  create: (exerciceSeanceId, data) =>
    api.post(`/exercice-seances/${exerciceSeanceId}/series`, data),
  delete: (id) => api.delete(`/series/${id}`),
};

// ── Progression ───────────────────────────────────────────────────────────────
export const progressionService = {
  get: (exerciceId)  => api.get(`/progression/${exerciceId}`),
  dashboard: ()      => api.get('/tableau-de-bord'),
};

export default api;