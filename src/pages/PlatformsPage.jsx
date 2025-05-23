import { useEffect } from 'react';
import PlatformList from '../components/platforms/PlatformList';

export default function PlatformsPage() {
  useEffect(() => {
    document.title = 'Platform Integrations | AI Auto Review';
  }, []);

  return <PlatformList />;
}
