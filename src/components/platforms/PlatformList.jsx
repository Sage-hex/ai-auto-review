import { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import axios from 'axios';
import { 
  LinkIcon, 
  ExternalLinkIcon, 
  TrashIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/react/solid';
import PlatformConnectForm from './PlatformConnectForm';

export default function PlatformList() {
  const { currentUser, business } = useAuth();
  const [platforms, setPlatforms] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showConnectModal, setShowConnectModal] = useState(false);
  const [selectedPlatform, setSelectedPlatform] = useState(null);
  
  // Check if user has admin or manager privileges
  const canManagePlatforms = ['admin', 'manager'].includes(currentUser?.role);
  
  useEffect(() => {
    fetchPlatforms();
  }, []);
  
  const fetchPlatforms = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.get('/backend/api/platforms');
      setPlatforms(response.data.data);
    } catch (err) {
      console.error('Error fetching platforms:', err);
      setError(err.response?.data?.message || 'Failed to fetch platforms');
    } finally {
      setLoading(false);
    }
  };
  
  const handleConnectPlatform = (platform) => {
    setSelectedPlatform(platform);
    setShowConnectModal(true);
  };
  
  const handleDisconnectPlatform = async (platform) => {
    if (!canManagePlatforms) return;
    
    if (!window.confirm(`Are you sure you want to disconnect ${platform}?`)) {
      return;
    }
    
    try {
      setLoading(true);
      setError('');
      
      await axios.delete(`/backend/api/platforms/${platform}`);
      
      // Refresh platforms list
      fetchPlatforms();
    } catch (err) {
      console.error('Error disconnecting platform:', err);
      setError(err.response?.data?.message || 'Failed to disconnect platform');
    } finally {
      setLoading(false);
    }
  };
  
  const handlePlatformConnected = () => {
    setShowConnectModal(false);
    setSelectedPlatform(null);
    fetchPlatforms();
  };
  
  const handleModalClose = () => {
    setShowConnectModal(false);
    setSelectedPlatform(null);
  };
  
  // Get platform icon and color
  const getPlatformDetails = (platform) => {
    switch (platform) {
      case 'google':
        return {
          name: 'Google My Business',
          icon: '🔍',
          color: 'bg-blue-100 text-blue-800 border-blue-200',
          activeColor: 'bg-blue-500 text-white',
          description: 'Connect to Google My Business to fetch and respond to Google reviews.'
        };
      case 'yelp':
        return {
          name: 'Yelp',
          icon: '🍽️',
          color: 'bg-red-100 text-red-800 border-red-200',
          activeColor: 'bg-red-500 text-white',
          description: 'Connect to Yelp to fetch and respond to Yelp reviews.'
        };
      case 'facebook':
        return {
          name: 'Facebook',
          icon: '👍',
          color: 'bg-indigo-100 text-indigo-800 border-indigo-200',
          activeColor: 'bg-indigo-500 text-white',
          description: 'Connect to Facebook to fetch and respond to Facebook reviews.'
        };
      default:
        return {
          name: platform,
          icon: '🔗',
          color: 'bg-gray-100 text-gray-800 border-gray-200',
          activeColor: 'bg-gray-500 text-white',
          description: 'Connect to fetch and respond to reviews.'
        };
    }
  };
  
  // Check if platform is available in current subscription plan
  const isPlatformAvailable = (platform) => {
    const planPlatforms = {
      'free': ['google'],
      'basic': ['google', 'yelp'],
      'pro': ['google', 'yelp', 'facebook']
    };
    
    return planPlatforms[business?.subscription_plan]?.includes(platform) || false;
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Platform Integrations</h1>
      </div>
      
      {/* Subscription Plan Info */}
      <div className="bg-white shadow rounded-lg p-6 mb-6">
        <h2 className="text-lg font-medium text-gray-900 mb-2">Available Platforms</h2>
        <p className="text-sm text-gray-500 mb-4">
          Your {business?.subscription_plan} plan includes access to:{' '}
          {business?.subscription_plan === 'free' ? 'Google My Business' : 
           business?.subscription_plan === 'basic' ? 'Google My Business and Yelp' : 
           'Google My Business, Yelp, and Facebook'}.
        </p>
        {business?.subscription_plan !== 'pro' && (
          <p className="text-sm text-primary-600">
            Upgrade your plan to access more platforms.
          </p>
        )}
      </div>
      
      {/* Error Message */}
      {error && (
        <div className="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
          <div className="flex">
            <div className="ml-3">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          </div>
        </div>
      )}
      
      {/* Platforms List */}
      {loading ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {['google', 'yelp', 'facebook'].map((platform) => {
            const platformDetails = getPlatformDetails(platform);
            const platformData = platforms.find(p => p.platform === platform);
            const isConnected = platformData?.connected || false;
            const isAvailable = isPlatformAvailable(platform);
            
            return (
              <div 
                key={platform}
                className={`bg-white shadow rounded-lg overflow-hidden ${!isAvailable ? 'opacity-60' : ''}`}
              >
                <div className={`px-6 py-4 border-b ${platformDetails.color}`}>
                  <div className="flex justify-between items-center">
                    <h3 className="text-lg font-medium">
                      <span className="mr-2">{platformDetails.icon}</span>
                      {platformDetails.name}
                    </h3>
                    {isConnected ? (
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <CheckCircleIcon className="h-4 w-4 mr-1" />
                        Connected
                      </span>
                    ) : (
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <XCircleIcon className="h-4 w-4 mr-1" />
                        Not Connected
                      </span>
                    )}
                  </div>
                </div>
                <div className="p-6">
                  <p className="text-sm text-gray-500 mb-4">
                    {platformDetails.description}
                  </p>
                  
                  {isAvailable ? (
                    <div className="flex justify-between">
                      {isConnected ? (
                        <>
                          <a
                            href={`https://${platform}.com`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center text-sm text-primary-600 hover:text-primary-700"
                          >
                            <ExternalLinkIcon className="h-4 w-4 mr-1" />
                            Visit {platformDetails.name}
                          </a>
                          {canManagePlatforms && (
                            <button
                              onClick={() => handleDisconnectPlatform(platform)}
                              className="inline-flex items-center text-sm text-red-600 hover:text-red-700"
                            >
                              <TrashIcon className="h-4 w-4 mr-1" />
                              Disconnect
                            </button>
                          )}
                        </>
                      ) : (
                        canManagePlatforms && (
                          <button
                            onClick={() => handleConnectPlatform(platform)}
                            className="inline-flex items-center text-sm text-primary-600 hover:text-primary-700"
                          >
                            <LinkIcon className="h-4 w-4 mr-1" />
                            Connect
                          </button>
                        )
                      )}
                    </div>
                  ) : (
                    <p className="text-sm text-gray-400">
                      Upgrade your subscription to access this platform.
                    </p>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}
      
      {/* Connect Platform Modal */}
      {showConnectModal && selectedPlatform && (
        <PlatformConnectForm
          platform={selectedPlatform}
          onConnect={handlePlatformConnected}
          onCancel={handleModalClose}
        />
      )}
    </div>
  );
}
