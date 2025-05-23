import { useEffect } from 'react';
import SubscriptionPlans from '../components/subscription/SubscriptionPlans';

export default function SubscriptionPage() {
  useEffect(() => {
    document.title = 'Subscription Plans | AI Auto Review';
  }, []);

  return <SubscriptionPlans />;
}
