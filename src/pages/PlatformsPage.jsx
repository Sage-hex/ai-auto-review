import { useEffect } from 'react';
import Layout from '../components/layout/Layout';
import PlatformList from '../components/platforms/PlatformList';

export default function PlatformsPage() {
  useEffect(() => {
    document.title = 'Platform Integrations | AI Auto Review';
  }, []);

  return (
    <Layout>
      <PlatformList />
    </Layout>
  );
}
