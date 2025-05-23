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
    // Check if user is already logged in
    const token = localStorage.getItem('token');
    const userData = localStorage.getItem('user');
    const businessData = localStorage.getItem('business');
    
    if (token && userData && businessData) {
      setCurrentUser(JSON.parse(userData));
      setBusiness(JSON.parse(businessData));
      
      // Authorization is handled by the API utility interceptor
      // No need to set headers manually
    }
    
    setLoading(false);
  }, []);

  // Login function
  const login = async (email, password) => {
    try {
      setError('');
      setLoading(true);
      
      // Make API request to login endpoint
      const response = await api.post('/backend/api/endpoints/auth/login.php', { 
        email, 
        password 
      });
      
      // Check if response is successful
      if (response.data && response.data.status === 'success') {
        const { user, business, token } = response.data.data;
        
        // Save to state
        setCurrentUser(user);
        setBusiness(business);
        
        // Save to localStorage
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('business', JSON.stringify(business));
        
        // Authorization is handled by the API utility interceptor
        // No need to set headers manually
        
        console.log('Login successful');
        
        return { user, business };
      } else {
        // Handle unexpected response format
        throw new Error(response.data?.message || 'Login failed. Unexpected response format.');
      }
    } catch (err) {
      console.error('Login error:', err);
      
      // Extract error message from response if available
      const errorMessage = err.response?.data?.message || err.message || 'Failed to login. Please check your credentials.';
      setError(errorMessage);
      
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Register function
  const register = async (businessName, name, email, password) => {
    try {
      setError('');
      setLoading(true);
      
      console.log('Registering with API:', { businessName, name, email });
      
      // Make API request to register endpoint
      const response = await api.post('/backend/api/endpoints/auth/register.php', {
        business_name: businessName,
        name,
        email,
        password
      });
      
      console.log('Registration response:', response.data);
      
      // Check if response is successful
      if (response.data && response.data.status === 'success') {
        const { user, business, token } = response.data.data;
        
        // Save to state
        setCurrentUser(user);
        setBusiness(business);
        
        // Save to localStorage
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('business', JSON.stringify(business));
        
        console.log('Registration successful');
        
        return { user, business };
      } else {
        // Handle unexpected response format
        const errorMsg = response.data?.message || 'Registration failed. Unexpected response format.';
        console.error('Registration response error:', errorMsg);
        throw new Error(errorMsg);
      }
    } catch (err) {
      console.error('Registration error:', err.response?.data || err.message || err);
      
      // Extract error message from response if available
      const errorMessage = err.response?.data?.message || err.message || 'Failed to register. Please try again.';
      setError(errorMessage);
      
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Logout function
  const logout = () => {
    setCurrentUser(null);
    setBusiness(null);
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('business');
    // Authorization is handled by the API utility interceptor
    // No need to clear headers manually
  };

  // Update business
  const updateBusiness = (updatedBusiness) => {
    setBusiness(updatedBusiness);
    localStorage.setItem('business', JSON.stringify(updatedBusiness));
  };

  const value = {
    currentUser,
    business,
    login,
    register,
    logout,
    updateBusiness,
    loading,
    error
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}
