import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../utils/api';

const AuthContext = createContext();

export function useAuth() {
  return useContext(AuthContext);
}

export function AuthProvider({ children }) {
  const [currentUser, setCurrentUser] = useState(null);
  const [business, setBusiness] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const token = localStorage.getItem('token');
    const userData = localStorage.getItem('user');
    const businessData = localStorage.getItem('business');

    if (token && userData && businessData) {
      setCurrentUser(JSON.parse(userData));
      setBusiness(JSON.parse(businessData));
    }

    setLoading(false);
  }, []);

  const login = async (email, password) => {
    setError('');
    setLoading(true);
    try {
      const response = await api.post('/auth/login', { email, password });
      const { user, business: businessPayload, token } = response.data.data;

      setCurrentUser(user);
      setBusiness(businessPayload);

      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('business', JSON.stringify(businessPayload));

      return { user, business: businessPayload };
    } catch (err) {
      const message = err.response?.data?.detail || err.response?.data?.message || 'Failed to login';
      setError(message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const register = async (businessName, name, email, password) => {
    setError('');
    setLoading(true);
    try {
      const response = await api.post('/auth/register', {
        business_name: businessName,
        name,
        email,
        password,
      });

      const { user, business: businessPayload, token } = response.data.data;
      setCurrentUser(user);
      setBusiness(businessPayload);

      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('business', JSON.stringify(businessPayload));

      return response.data;
    } catch (err) {
      const message = err.response?.data?.detail || err.response?.data?.message || 'Failed to register';
      setError(message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const loginWithToken = (token, userData) => {
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(userData));
    setCurrentUser(userData);
  };

  const logout = () => {
    setCurrentUser(null);
    setBusiness(null);
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('business');
  };

  const updateBusiness = (updatedBusiness) => {
    setBusiness(updatedBusiness);
    localStorage.setItem('business', JSON.stringify(updatedBusiness));
  };

  return (
    <AuthContext.Provider
      value={{
        currentUser,
        business,
        loading,
        error,
        login,
        register,
        loginWithToken,
        logout,
        updateBusiness,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}
