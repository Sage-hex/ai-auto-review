import { useEffect } from 'react';
import Settings from '../components/common/Settings';

export default function SettingsPage() {
  useEffect(() => {
    document.title = 'Settings | AI Auto Review';
  }, []);

  return <Settings />;
}
