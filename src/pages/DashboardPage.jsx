import { useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import Layout from '../components/layout/Layout';
import Dashboard from '../components/dashboard/Dashboard';
import Analytics from '../components/dashboard/Analytics';

export default function DashboardPage() {
  const { currentUser, loading } = useAuth();
  
  useEffect(() => {
    document.title = 'Dashboard | AI Auto Review';
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  return (
    <Layout>
      <div className="space-y-6">
        <Dashboard />
        <Analytics />
      </div>
    </Layout>
  );
}
