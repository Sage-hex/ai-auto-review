import axios from 'axios';

// Create an axios instance for public routes without auth
const publicApi = axios.create({
  baseURL: '',
  headers: {
    'Content-Type': 'application/json',
  },
  timeout: 10000,
  transformResponse: [(data) => {
    if (!data) return {};
    try {
      return JSON.parse(data);
    } catch (error) {
      console.error('Error parsing JSON response:', error);
      return { status: 'error', message: 'Invalid JSON response from server', rawData: data };
    }
  }],
});

export default publicApi;
