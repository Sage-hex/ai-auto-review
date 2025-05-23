import { useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import UserList from '../components/users/UserList';

export default function UsersPage() {
  const { currentUser } = useAuth();
  
  useEffect(() => {
    document.title = 'User Management | AI Auto Review';
  }, []);

  return <UserList />;
}
