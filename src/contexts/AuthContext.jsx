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
      
      // Make API request to the auth handler
      const response = await api.post('/backend/api/endpoints/auth/handler.php', { 
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

  // Login with token and user data (used after OTP verification)
  const loginWithToken = (token, userData) => {
    try {
      setError('');
      
      if (!token || !userData) {
        throw new Error('Token and user data are required');
      }
      
      // Save to state
      setCurrentUser(userData);
      
      // Save to localStorage
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(userData));
      
      // If business data is available, save it too
      if (userData.business_id) {
        const businessData = {
          id: userData.business_id,
          name: userData.business_name || 'Your Business'
        };
        setBusiness(businessData);
        localStorage.setItem('business', JSON.stringify(businessData));
      }
      
      console.log('Login with token successful');
      return userData;
    } catch (err) {
      console.error('Login with token error:', err);
      setError(err.message || 'Failed to login with token');
      throw err;
    }
  };

  // Register function
  const register = async (businessName, name, email, password) => {
    try {
      setError('');
      setLoading(true);
      
      console.log('Registering with API:', { businessName, name, email });
      
      // Validate input data before sending to API
      if (!businessName || !name || !email || !password) {
        throw new Error('All fields are required');
      }
      
      // Prepare request data
      const requestData = {
        business_name: businessName,
        name,
        email,
        password
      };
      
      console.log('Sending registration data:', requestData);
      console.log('API endpoint URL:', '/backend/api/endpoints/auth/handler.php');
      
      // Make API request to the auth handler
      const response = await api.post('/backend/api/endpoints/auth/handler.php', requestData);
      console.log('API URL used:', api.defaults.baseURL + '/backend/api/endpoints/auth/handler.php');
      
      console.log('Registration response:', response);
      
      // Check if response exists
      if (!response || !response.data) {
        console.error('Empty response received from server');
        // Don't throw here - it might be a network error but the registration could have succeeded
        return { status: 'warning', message: 'Server returned an empty response, but your account may have been created. Please try logging in.' };
      }
      
      // Check if response has error status from our custom transform
      if (response.data.status === 'error' && response.data.message === 'Invalid JSON response from server') {
        console.error('Invalid JSON response:', response.data.rawData);
        throw new Error('Server returned an invalid response. Please try again.');
      }
      
      // Check if response is successful
      if (response.data && response.data.status === 'success') {
        // Check if verification is required
        if (response.data.data.verification_required) {
          console.log('Registration successful, verification required');
          // Return the response data without setting user state
          // User will be redirected to OTP verification page
          return response.data;
        } else {
          // Traditional flow - no verification required
          const { user, business, token } = response.data.data;
          
          // Validate required data
          if (!user || !business || !token) {
            throw new Error('Registration succeeded but returned incomplete data');
          }
          
          // Save to state
          setCurrentUser(user);
          setBusiness(business);
          
          // Save to localStorage
          localStorage.setItem('token', token);
          localStorage.setItem('user', JSON.stringify(user));
          localStorage.setItem('business', JSON.stringify(business));
          
          console.log('Registration successful');
          
          return response.data;
        }
      } else {
        // Handle unexpected response format
        const errorMsg = response.data?.message || 'Registration failed. Unexpected response format.';
        console.error('Registration response error:', errorMsg);
        throw new Error(errorMsg);
      }
    } catch (err) {
      console.error('Registration error:', err);
      
      // Log detailed error information
      if (err.response) {
        console.error('Error response:', err.response);
        console.error('Error response data:', err.response.data);
        console.error('Error response status:', err.response.status);
        console.error('Error response headers:', err.response.headers);
      } else if (err.request) {
        // The request was made but no response was received
        console.error('Error request:', err.request);
      }
      
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
    loading,
    error,
    login,
    loginWithToken,
    register,
    logout,
    updateBusiness
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}
