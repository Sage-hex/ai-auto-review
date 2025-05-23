import { useEffect } from 'react';
import PremiumNotFound from '../components/common/PremiumNotFound';

export default function NotFoundPage() {
  useEffect(() => {
    document.title = '404 - Page Not Found | AI Auto Review';
  }, []);

  return <PremiumNotFound />;
}
