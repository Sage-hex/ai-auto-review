import axios from 'axios';

// Create an axios instance with a base URL
// Using the proxy configured in vite.config.js
const api = axios.create({
  baseURL: 'http://localhost/AiAutoReview',  // Point directly to the XAMPP server
  headers: {
    'Content-Type': 'application/json',
  },
  // Add timeout to prevent hanging requests
  timeout: 10000,
  // Ensure proper JSON parsing
  transformResponse: [(data) => {
    // If the response is empty, return an empty object
    if (!data) return {};
    
    try {
      return JSON.parse(data);
    } catch (error) {
      console.error('Error parsing JSON response:', error);
      console.error('Raw response:', data);
      // Return a structured error object instead of throwing
      return { status: 'error', message: 'Invalid JSON response from server', rawData: data };
    }
  }],
});

// Add a request interceptor to handle authentication
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add a response interceptor to handle common errors
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    console.error('API Error:', error);
    
    // No response from server
    if (!error.response) {
      console.error('Network error or no response from server');
      return Promise.reject({
        response: {
          data: {
            status: 'error',
            message: 'Network error or no response from server. Please check your connection.'
          }
        }
      });
    }
    
    // Empty response
    if (error.response && !error.response.data) {
      console.error('Empty response from server');
      error.response.data = {
        status: 'error',
        message: 'Empty response from server'
      };
    }
    
    // Handle common errors like 401 Unauthorized
    if (error.response && error.response.status === 401) {
      // Clear local storage and redirect to login
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('business');
      window.location.href = '/login';
    }
    
    return Promise.reject(error);
  }
);

export default api;
