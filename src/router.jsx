import { Navigate, createBrowserRouter, Outlet } from 'react-router-dom';
import { useAuth } from './contexts/AuthContext';

// Layout
import Layout from './components/layout/Layout';

// Pages
import LandingPage from './pages/LandingPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import ForgotPasswordPage from './pages/ForgotPasswordPage';
import ResetPasswordPage from './pages/ResetPasswordPage';
import DashboardPage from './pages/DashboardPage';
import ReviewsPage from './pages/ReviewsPage';
import ReviewDetailPage from './pages/ReviewDetailPage';
import AnalyticsPage from './pages/AnalyticsPage';
import UsersPage from './pages/UsersPage';
import PlatformsPage from './pages/PlatformsPage';
import ResponsesPage from './pages/ResponsesPage';
import SubscriptionPage from './pages/SubscriptionPage';
import SettingsPage from './pages/SettingsPage';
import NotFoundPage from './pages/NotFoundPage';

// Import RouteTransition component
import RouteTransition from './components/common/RouteTransition';

// Protected Route wrapper component
const ProtectedRoute = ({ children }) => {
  const { currentUser, loading } = useAuth();
  
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
      </div>
    );
  }
  
  if (!currentUser) {
    return <Navigate to="/login" replace />;
  }
  
  return <RouteTransition>{children}</RouteTransition>;
};

// Define routes
const routes = [
  // Public Routes
  {
    path: '/',
    element: <LandingPage />
  },
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
  {
    path: '/reset-password',
    element: <ResetPasswordPage />
  },
  
  // Protected Dashboard Route with nested routes
  {
    path: '/dashboard',
    element: <ProtectedRoute>
      <Layout>
        <Outlet />
      </Layout>
    </ProtectedRoute>,
    children: [
      {
        index: true,
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
        path: 'analytics',
        element: <AnalyticsPage />
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
        path: 'responses',
        element: <ResponsesPage />
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

// Create and export the router
export const router = createBrowserRouter(routes);
