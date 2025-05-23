import { Outlet } from 'react-router-dom';
import Sidebar from './Sidebar';
import { useAuth } from '../../contexts/AuthContext';

export default function Layout({ children }) {
  const { currentUser } = useAuth();

  if (!currentUser) {
    return children || <Outlet />;
  }

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar />
      
      {/* Main content */}
      <div className="flex-1 overflow-auto">
        <main className="p-6">
          <div className="max-w-7xl mx-auto">
            {children || <Outlet />}
          </div>
        </main>
      </div>
    </div>
  );
}
