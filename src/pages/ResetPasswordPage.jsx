import { useEffect } from 'react';
import ResetPassword from '../components/auth/ResetPassword';
import '../styles/premium-animations.css';

export default function ResetPasswordPage() {
  useEffect(() => {
    document.title = 'Reset Password | AI Auto Review';
  }, []);

  return <ResetPassword />;
}
