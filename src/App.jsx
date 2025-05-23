import { BrowserRouter as Router, Routes, Route, useRoutes } from 'react-router-dom';
import axios from 'axios';
import './App.css';

// Context Providers
import { AuthProvider } from './contexts/AuthContext';
import { ReviewProvider } from './contexts/ReviewContext';

// Import routes
import routes from './router';

// Set base URL for API requests
axios.defaults.baseURL = 'http://localhost/AiAutoReview';

// AppRoutes component to use the useRoutes hook
const AppRoutes = () => {
  const element = useRoutes(routes);
  return element;
};

function App() {
  return (
    <Router>
      <AuthProvider>
        <ReviewProvider>
          <AppRoutes />
        </ReviewProvider>
      </AuthProvider>
    </Router>
  );
}

export default App
