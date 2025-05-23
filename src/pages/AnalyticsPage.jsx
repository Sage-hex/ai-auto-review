import { useEffect } from 'react';
import Analytics from '../components/dashboard/Analytics';

export default function AnalyticsPage() {
  useEffect(() => {
    document.title = 'Analytics | AI Auto Review';
  }, []);

  return <Analytics />;
}
