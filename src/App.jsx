import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import axios from 'axios';
import './App.css';

// Context Providers
import { AuthProvider } from './contexts/AuthContext';
import { ReviewProvider } from './contexts/ReviewContext';

// Auth Components
import ProtectedRoute from './components/auth/ProtectedRoute';

// Layout
import Layout from './components/layout/Layout';

// Pages
import LandingPage from './pages/LandingPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import ForgotPasswordPage from './pages/ForgotPasswordPage';
import DashboardPage from './pages/DashboardPage';
import ReviewsPage from './pages/ReviewsPage';
import ReviewDetailPage from './pages/ReviewDetailPage';
import UsersPage from './pages/UsersPage';
import PlatformsPage from './pages/PlatformsPage';
import SubscriptionPage from './pages/SubscriptionPage';
import SettingsPage from './pages/SettingsPage';
import NotFoundPage from './pages/NotFoundPage';

// Set base URL for API requests
axios.defaults.baseURL = 'http://localhost/AiAutoReview';

function App() {
  return (
    <Router>
      <AuthProvider>
        <ReviewProvider>
          <Routes>
            {/* Public Routes */}
            <Route path="/landing" element={<LandingPage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/forgot-password" element={<ForgotPasswordPage />} />
            
            {/* Protected Routes with Layout */}
            <Route path="/" element={<ProtectedRoute><Layout /></ProtectedRoute>}>
              <Route index element={<DashboardPage />} />
              <Route path="dashboard" element={<DashboardPage />} />
              <Route path="reviews" element={<ReviewsPage />} />
              <Route path="reviews/:id" element={<ReviewDetailPage />} />
              <Route path="users" element={<UsersPage />} />
              <Route path="platforms" element={<PlatformsPage />} />
              <Route path="subscription" element={<SubscriptionPage />} />
              <Route path="settings" element={<SettingsPage />} />
            </Route>
            
            {/* 404 Route */}
            <Route path="*" element={<NotFoundPage />} />
          </Routes>
        </ReviewProvider>
      </AuthProvider>
    </Router>
  );
}

export default App
