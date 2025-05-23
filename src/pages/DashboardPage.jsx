import { useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import Dashboard from '../components/dashboard/Dashboard';

export default function DashboardPage() {
  const { currentUser, loading } = useAuth();
  
  useEffect(() => {
    document.title = 'Dashboard | AI Auto Review';
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center h-full">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  return (
    <>
      <Dashboard />
    </>
  );
}
