import { useState } from 'react';
import axios from 'axios';
import { XIcon } from '@heroicons/react/solid';

export default function PlatformConnectForm({ platform, onConnect, onCancel }) {
  const [credentials, setCredentials] = useState({});
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  
  // Get platform details
  const getPlatformDetails = (platform) => {
    switch (platform) {
      case 'google':
        return {
          name: 'Google My Business',
          icon: '🔍',
          color: 'bg-blue-100 text-blue-800 border-blue-200',
          fields: [
            { name: 'client_id', label: 'Client ID', type: 'text', required: true },
            { name: 'client_secret', label: 'Client Secret', type: 'password', required: true },
            { name: 'location_id', label: 'Location ID', type: 'text', required: true }
          ]
        };
      case 'yelp':
        return {
          name: 'Yelp',
          icon: '🍽️',
          color: 'bg-red-100 text-red-800 border-red-200',
          fields: [
            { name: 'api_key', label: 'API Key', type: 'password', required: true },
            { name: 'business_id', label: 'Business ID', type: 'text', required: true }
          ]
        };
      case 'facebook':
        return {
          name: 'Facebook',
          icon: '👍',
          color: 'bg-indigo-100 text-indigo-800 border-indigo-200',
          fields: [
            { name: 'access_token', label: 'Access Token', type: 'password', required: true },
            { name: 'page_id', label: 'Page ID', type: 'text', required: true }
          ]
        };
      default:
        return {
          name: platform,
          icon: '🔗',
          color: 'bg-gray-100 text-gray-800 border-gray-200',
          fields: []
        };
    }
  };
  
  const platformDetails = getPlatformDetails(platform);
  
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setCredentials(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validation
    const missingFields = platformDetails.fields
      .filter(field => field.required && !credentials[field.name])
      .map(field => field.label);
    
    if (missingFields.length > 0) {
      setError(`Please fill in the following fields: ${missingFields.join(', ')}`);
      return;
    }
    
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.post(`/backend/api/platforms/${platform}/connect`, credentials);
      
      onConnect(response.data.data);
    } catch (err) {
      console.error(`Error connecting to ${platform}:`, err);
      setError(err.response?.data?.message || `Failed to connect to ${platformDetails.name}`);
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <div className="fixed inset-0 overflow-y-auto z-50">
      <div className="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div className="fixed inset-0 transition-opacity" aria-hidden="true">
          <div className="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <span className="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div className="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div className="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-lg leading-6 font-medium text-gray-900">
                <span className="mr-2">{platformDetails.icon}</span>
                Connect to {platformDetails.name}
              </h3>
              <button
                onClick={onCancel}
                className="text-gray-400 hover:text-gray-500"
              >
                <XIcon className="h-6 w-6" />
              </button>
            </div>
            
            {error && (
              <div className="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                <div className="flex">
                  <div className="ml-3">
                    <p className="text-sm text-red-700">{error}</p>
                  </div>
                </div>
              </div>
            )}
            
            <p className="text-sm text-gray-500 mb-4">
              Please provide the necessary credentials to connect to {platformDetails.name}.
            </p>
            
            <form onSubmit={handleSubmit}>
              {platformDetails.fields.map((field) => (
                <div key={field.name} className="mb-4">
                  <label htmlFor={field.name} className="block text-sm font-medium text-gray-700 mb-1">
                    {field.label} {field.required && <span className="text-red-500">*</span>}
                  </label>
                  <input
                    type={field.type}
                    id={field.name}
                    name={field.name}
                    value={credentials[field.name] || ''}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    placeholder={field.label}
                    required={field.required}
                  />
                </div>
              ))}
              
              <div className="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                  type="submit"
                  disabled={loading}
                  className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
                >
                  {loading ? 'Connecting...' : 'Connect'}
                </button>
                <button
                  type="button"
                  onClick={onCancel}
                  className="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
