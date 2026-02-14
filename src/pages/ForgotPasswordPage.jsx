import { useEffect } from 'react';
import ForgotPassword from '../components/auth/ForgotPassword';
import '../styles/premium-animations.css';

export default function ForgotPasswordPage() {
  useEffect(() => {
    document.title = 'Forgot Password | AI Auto Review';
  }, []);

  return <ForgotPassword />;
}
