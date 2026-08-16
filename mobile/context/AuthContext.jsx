import { createContext, useContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { authService, profilService } from '../services/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser]       = useState(null);
  const [token, setToken]     = useState(null);
  const [loading, setLoading] = useState(true);

  // ── Au démarrage — vérifier si un token existe ──────────────────────────
  useEffect(() => {
    checkToken();
  }, []);

  const checkToken = async () => {
    try {
      const savedToken = await AsyncStorage.getItem('jwt_token');
      if (savedToken) {
        setToken(savedToken);
        // Récupérer le profil utilisateur
        const response = await profilService.get();
        setUser(response.data);
      }
    } catch (error) {
      await AsyncStorage.removeItem('jwt_token');
    } finally {
      setLoading(false);
    }
  };

  // ── Inscription ──────────────────────────────────────────────────────────
  const register = async (email, motDePasse, prenom, nom) => {
    const response = await authService.register({
      email,
      mot_de_passe: motDePasse,
      prenom,
      nom,
    });
    return response.data;
  };

  // ── Connexion ────────────────────────────────────────────────────────────
  const login = async (email, motDePasse) => {
    const response = await authService.login(email, motDePasse);
    setToken(response.data.token);
    // Récupérer le profil
    const profil = await profilService.get();
    setUser(profil.data);
    return response.data;
  };

  // ── Déconnexion ──────────────────────────────────────────────────────────
  const logout = async () => {
    await authService.logout();
    setToken(null);
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, token, loading, login, logout, register }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth doit être utilisé dans un AuthProvider');
  }
  return context;
}