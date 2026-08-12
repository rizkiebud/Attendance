import React, {createContext, useContext, useState, useEffect} from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {authService} from '../services/api';
import {on} from '../utils/events';

const AuthContext = createContext(null);

export const AuthProvider = ({children}) => {
  const [user, setUser] = useState(null);
  const [employee, setEmployee] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  useEffect(() => {
    checkAuthState();
    const off401 = on('auth:unauthorized', () => {
      setUser(null);
      setEmployee(null);
      setIsAuthenticated(false);
    });
    return off401;
  }, []);

  const checkAuthState = async () => {
    try {
      const token = await AsyncStorage.getItem('auth_token');
      const userData = await AsyncStorage.getItem('user_data');

      if (token && userData) {
        try {
          const parsed = JSON.parse(userData);
          setUser(parsed);
          setEmployee(parsed.employee);
          setIsAuthenticated(true);
        } catch {
          // Corrupt stored user_data — clear token so user isn't stuck logged out
          await AsyncStorage.multiRemove(['auth_token', 'user_data']);
        }
      }
    } catch (error) {
      console.error('Auth state check error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const login = async (username, password) => {
    try {
      const response = await authService.login(username, password);
      const {token, user: userData} = response.data.data;

      await AsyncStorage.setItem('auth_token', token);
      await AsyncStorage.setItem('user_data', JSON.stringify(userData));

      setUser(userData);
      setEmployee(userData.employee);
      setIsAuthenticated(true);

      return {success: true};
    } catch (error) {
      const message =
        error.response?.data?.message || 'Terjadi kesalahan saat login';
      return {success: false, message};
    }
  };

  const logout = async () => {
    try {
      await authService.logout();
    } catch {}
    await AsyncStorage.multiRemove(['auth_token', 'user_data']);
    setUser(null);
    setEmployee(null);
    setIsAuthenticated(false);
  };

  const refreshUser = async () => {
    try {
      const response = await authService.me();
      const userData = response.data.data;
      await AsyncStorage.setItem('user_data', JSON.stringify(userData));
      setUser(userData);
      setEmployee(userData.employee);
      return {success: true};
    } catch (error) {
      // If 401, interceptor already logged out; surface other errors
      if (error.response?.status !== 401) {
        console.error('Refresh user error:', error);
      }
      return {success: false};
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        employee,
        isLoading,
        isAuthenticated,
        canApproveLeave: user?.can_approve_leave === true,
        login,
        logout,
        refreshUser,
      }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
