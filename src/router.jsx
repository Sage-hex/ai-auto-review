import { Navigate } from 'react-router-dom';

// Layout
import Layout from './components/layout/Layout';

// Auth Components
import ProtectedRoute from './components/auth/ProtectedRoute';

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

const routes = [
  // Public Routes
  {
    path: '/landing',
    element: <LandingPage />
  },
  {
    path: '/login',
    element: <LoginPage />
  },
  {
    path: '/register',
    element: <RegisterPage />
  },
  {
    path: '/forgot-password',
    element: <ForgotPasswordPage />
  },
  
  // Protected Routes with Layout
  {
    path: '/',
    element: <ProtectedRoute><Layout /></ProtectedRoute>,
    children: [
      {
        index: true,
        element: <DashboardPage />
      },
      {
        path: 'dashboard',
        element: <DashboardPage />
      },
      {
        path: 'reviews',
        element: <ReviewsPage />
      },
      {
        path: 'reviews/:id',
        element: <ReviewDetailPage />
      },
      {
        path: 'users',
        element: <UsersPage />
      },
      {
        path: 'platforms',
        element: <PlatformsPage />
      },
      {
        path: 'subscription',
        element: <SubscriptionPage />
      },
      {
        path: 'settings',
        element: <SettingsPage />
      }
    ]
  },
  
  // 404 Route
  {
    path: '*',
    element: <NotFoundPage />
  }
];

export default routes;
