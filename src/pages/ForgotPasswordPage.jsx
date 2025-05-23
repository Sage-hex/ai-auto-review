import { useEffect } from 'react';
import PremiumForgotPassword from '../components/auth/PremiumForgotPassword';

export default function ForgotPasswordPage() {
  useEffect(() => {
    document.title = 'Forgot Password | AI Auto Review';
  }, []);

  return <PremiumForgotPassword />;
}
