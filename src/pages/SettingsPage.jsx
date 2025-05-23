import { useEffect } from 'react';
import Layout from '../components/layout/Layout';
import Settings from '../components/common/Settings';

export default function SettingsPage() {
  useEffect(() => {
    document.title = 'Settings | AI Auto Review';
  }, []);

  return (
    <Layout>
      <Settings />
    </Layout>
  );
}
