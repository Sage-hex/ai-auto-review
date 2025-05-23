import { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import axios from 'axios';
import { SaveIcon } from '@heroicons/react/solid';

export default function Settings() {
  const { currentUser, business, refreshUserData } = useAuth();
  const [businessSettings, setBusinessSettings] = useState({
    name: '',
    email: '',
    phone: '',
    address: '',
    website: '',
    logo_url: ''
  });
  const [aiSettings, setAiSettings] = useState({
    response_tone: 'professional',
    response_length: 'medium',
    include_business_name: true,
    auto_respond_to_positive: false,
    auto_respond_to_negative: false
  });
  const [notificationSettings, setNotificationSettings] = useState({
    email_notifications: true,
    new_review_notification: true,
    negative_review_notification: true,
    weekly_summary: true
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  
  // Check if user has admin or manager privileges
  const canEditSettings = ['admin', 'manager'].includes(currentUser?.role);
  
  useEffect(() => {
    fetchSettings();
  }, []);
  
  const fetchSettings = async () => {
    try {
      setLoading(true);
      setError('');
      
      // Fetch business settings
      const businessResponse = await axios.get('/backend/api/business/settings');
      setBusinessSettings(businessResponse.data.data);
      
      // Fetch AI settings
      const aiResponse = await axios.get('/backend/api/settings/ai');
      setAiSettings(aiResponse.data.data);
      
      // Fetch notification settings
      const notificationResponse = await axios.get('/backend/api/settings/notifications');
      setNotificationSettings(notificationResponse.data.data);
    } catch (err) {
      console.error('Error fetching settings:', err);
      setError(err.response?.data?.message || 'Failed to fetch settings');
    } finally {
      setLoading(false);
    }
  };
  
  const handleBusinessChange = (e) => {
    const { name, value } = e.target;
    setBusinessSettings(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const handleAIChange = (e) => {
    const { name, value, type, checked } = e.target;
    setAiSettings(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };
  
  const handleNotificationChange = (e) => {
    const { name, checked } = e.target;
    setNotificationSettings(prev => ({
      ...prev,
      [name]: checked
    }));
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!canEditSettings) return;
    
    try {
      setLoading(true);
      setError('');
      setSuccess('');
      
      // Update business settings
      await axios.put('/backend/api/business/settings', businessSettings);
      
      // Update AI settings
      await axios.put('/backend/api/settings/ai', aiSettings);
      
      // Update notification settings
      await axios.put('/backend/api/settings/notifications', notificationSettings);
      
      // Refresh user data
      await refreshUserData();
      
      setSuccess('Settings updated successfully');
    } catch (err) {
      console.error('Error updating settings:', err);
      setError(err.response?.data?.message || 'Failed to update settings');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Settings</h1>
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
      
      {/* Success Message */}
      {success && (
        <div className="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
          <div className="flex">
            <div className="ml-3">
              <p className="text-sm text-green-700">{success}</p>
            </div>
          </div>
        </div>
      )}
      
      {loading ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
        </div>
      ) : (
        <form onSubmit={handleSubmit}>
          <div className="space-y-6">
            {/* Business Settings */}
            <div className="bg-white shadow rounded-lg overflow-hidden">
              <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-lg font-medium text-gray-900">Business Information</h2>
              </div>
              <div className="p-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                      Business Name
                    </label>
                    <input
                      type="text"
                      id="name"
                      name="name"
                      value={businessSettings.name}
                      onChange={handleBusinessChange}
                      disabled={!canEditSettings}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                    />
                  </div>
                  <div>
                    <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                      Business Email
                    </label>
                    <input
                      type="email"
                      id="email"
                      name="email"
                      value={businessSettings.email}
                      onChange={handleBusinessChange}
                      disabled={!canEditSettings}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                    />
                  </div>
                  <div>
                    <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-1">
                      Phone Number
                    </label>
                    <input
                      type="text"
                      id="phone"
                      name="phone"
                      value={businessSettings.phone}
                      onChange={handleBusinessChange}
                      disabled={!canEditSettings}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                    />
                  </div>
                  <div>
                    <label htmlFor="website" className="block text-sm font-medium text-gray-700 mb-1">
                      Website
                    </label>
                    <input
                      type="url"
                      id="website"
                      name="website"
                      value={businessSettings.website}
                      onChange={handleBusinessChange}
                      disabled={!canEditSettings}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                    />
                  </div>
                  <div className="md:col-span-2">
                    <label htmlFor="address" className="block text-sm font-medium text-gray-700 mb-1">
                      Address
                    </label>
                    <input
                      type="text"
                      id="address"
                      name="address"
                      value={businessSettings.address}
                      onChange={handleBusinessChange}
                      disabled={!canEditSettings}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                    />
                  </div>
                  <div className="md:col-span-2">
                    <label htmlFor="logo_url" className="block text-sm font-medium text-gray-700 mb-1">
                      Logo URL
                    </label>
                    <input
                      type="url"
                      id="logo_url"
                      name="logo_url"
                      value={businessSettings.logo_url}
                      onChange={handleBusinessChange}
                      disabled={!canEditSettings}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                    />
                  </div>
                </div>
              </div>
            </div>
            
            {/* AI Response Settings */}
            <div className="bg-white shadow rounded-lg overflow-hidden">
              <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-lg font-medium text-gray-900">AI Response Settings</h2>
              </div>
              <div className="p-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label htmlFor="response_tone" className="block text-sm font-medium text-gray-700 mb-1">
                      Response Tone
                    </label>
                    <select
                      id="response_tone"
                      name="response_tone"
                      value={aiSettings.response_tone}
                      onChange={handleAIChange}
                      disabled={!canEditSettings}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                    >
                      <option value="professional">Professional</option>
                      <option value="friendly">Friendly</option>
                      <option value="casual">Casual</option>
                      <option value="formal">Formal</option>
                    </select>
                  </div>
                  <div>
                    <label htmlFor="response_length" className="block text-sm font-medium text-gray-700 mb-1">
                      Response Length
                    </label>
                    <select
                      id="response_length"
                      name="response_length"
                      value={aiSettings.response_length}
                      onChange={handleAIChange}
                      disabled={!canEditSettings}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                    >
                      <option value="short">Short</option>
                      <option value="medium">Medium</option>
                      <option value="long">Long</option>
                    </select>
                  </div>
                </div>
                
                <div className="mt-6 space-y-4">
                  <div className="flex items-start">
                    <div className="flex items-center h-5">
                      <input
                        id="include_business_name"
                        name="include_business_name"
                        type="checkbox"
                        checked={aiSettings.include_business_name}
                        onChange={handleAIChange}
                        disabled={!canEditSettings}
                        className="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded disabled:opacity-50"
                      />
                    </div>
                    <div className="ml-3 text-sm">
                      <label htmlFor="include_business_name" className="font-medium text-gray-700">
                        Include business name in responses
                      </label>
                      <p className="text-gray-500">Automatically include your business name in AI-generated responses.</p>
                    </div>
                  </div>
                  
                  <div className="flex items-start">
                    <div className="flex items-center h-5">
                      <input
                        id="auto_respond_to_positive"
                        name="auto_respond_to_positive"
                        type="checkbox"
                        checked={aiSettings.auto_respond_to_positive}
                        onChange={handleAIChange}
                        disabled={!canEditSettings}
                        className="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded disabled:opacity-50"
                      />
                    </div>
                    <div className="ml-3 text-sm">
                      <label htmlFor="auto_respond_to_positive" className="font-medium text-gray-700">
                        Auto-respond to positive reviews
                      </label>
                      <p className="text-gray-500">Automatically publish AI-generated responses to 4-5 star reviews.</p>
                    </div>
                  </div>
                  
                  <div className="flex items-start">
                    <div className="flex items-center h-5">
                      <input
                        id="auto_respond_to_negative"
                        name="auto_respond_to_negative"
                        type="checkbox"
                        checked={aiSettings.auto_respond_to_negative}
                        onChange={handleAIChange}
                        disabled={!canEditSettings}
                        className="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded disabled:opacity-50"
                      />
                    </div>
                    <div className="ml-3 text-sm">
                      <label htmlFor="auto_respond_to_negative" className="font-medium text-gray-700">
                        Auto-respond to negative reviews
                      </label>
                      <p className="text-gray-500">Automatically generate (but save as draft) responses to 1-3 star reviews.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            {/* Notification Settings */}
            <div className="bg-white shadow rounded-lg overflow-hidden">
              <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-lg font-medium text-gray-900">Notification Settings</h2>
              </div>
              <div className="p-6">
                <div className="space-y-4">
                  <div className="flex items-start">
                    <div className="flex items-center h-5">
                      <input
                        id="email_notifications"
                        name="email_notifications"
                        type="checkbox"
                        checked={notificationSettings.email_notifications}
                        onChange={handleNotificationChange}
                        disabled={!canEditSettings}
                        className="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded disabled:opacity-50"
                      />
                    </div>
                    <div className="ml-3 text-sm">
                      <label htmlFor="email_notifications" className="font-medium text-gray-700">
                        Email Notifications
                      </label>
                      <p className="text-gray-500">Receive email notifications for important events.</p>
                    </div>
                  </div>
                  
                  <div className="flex items-start">
                    <div className="flex items-center h-5">
                      <input
                        id="new_review_notification"
                        name="new_review_notification"
                        type="checkbox"
                        checked={notificationSettings.new_review_notification}
                        onChange={handleNotificationChange}
                        disabled={!canEditSettings || !notificationSettings.email_notifications}
                        className="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded disabled:opacity-50"
                      />
                    </div>
                    <div className="ml-3 text-sm">
                      <label htmlFor="new_review_notification" className="font-medium text-gray-700">
                        New Review Notifications
                      </label>
                      <p className="text-gray-500">Get notified when your business receives a new review.</p>
                    </div>
                  </div>
                  
                  <div className="flex items-start">
                    <div className="flex items-center h-5">
                      <input
                        id="negative_review_notification"
                        name="negative_review_notification"
                        type="checkbox"
                        checked={notificationSettings.negative_review_notification}
                        onChange={handleNotificationChange}
                        disabled={!canEditSettings || !notificationSettings.email_notifications}
                        className="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded disabled:opacity-50"
                      />
                    </div>
                    <div className="ml-3 text-sm">
                      <label htmlFor="negative_review_notification" className="font-medium text-gray-700">
                        Negative Review Alerts
                      </label>
                      <p className="text-gray-500">Get immediate alerts for 1-3 star reviews.</p>
                    </div>
                  </div>
                  
                  <div className="flex items-start">
                    <div className="flex items-center h-5">
                      <input
                        id="weekly_summary"
                        name="weekly_summary"
                        type="checkbox"
                        checked={notificationSettings.weekly_summary}
                        onChange={handleNotificationChange}
                        disabled={!canEditSettings || !notificationSettings.email_notifications}
                        className="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded disabled:opacity-50"
                      />
                    </div>
                    <div className="ml-3 text-sm">
                      <label htmlFor="weekly_summary" className="font-medium text-gray-700">
                        Weekly Summary
                      </label>
                      <p className="text-gray-500">Receive a weekly summary of your review activity.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            {/* Submit Button */}
            {canEditSettings && (
              <div className="flex justify-end">
                <button
                  type="submit"
                  disabled={loading}
                  className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                >
                  <SaveIcon className="h-5 w-5 mr-2" />
                  {loading ? 'Saving...' : 'Save Settings'}
                </button>
              </div>
            )}
          </div>
        </form>
      )}
    </div>
  );
}
