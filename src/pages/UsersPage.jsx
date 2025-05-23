import { useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import Layout from '../components/layout/Layout';
import UserList from '../components/users/UserList';

export default function UsersPage() {
  const { currentUser } = useAuth();
  
  useEffect(() => {
    document.title = 'User Management | AI Auto Review';
  }, []);

  return (
    <Layout>
      <UserList />
    </Layout>
  );
}
