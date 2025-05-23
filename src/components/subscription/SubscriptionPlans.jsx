import { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import axios from 'axios';
import { CheckIcon, XIcon } from '@heroicons/react/solid';

export default function SubscriptionPlans() {
  const { currentUser, business, refreshUserData } = useAuth();
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [processingPlan, setProcessingPlan] = useState(null);
  
  // Check if user has admin privileges
  const isAdmin = currentUser?.role === 'admin';
  
  useEffect(() => {
    fetchPlans();
  }, []);
  
  const fetchPlans = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.get('/backend/api/subscription/plans');
      setPlans(response.data.data);
    } catch (err) {
      console.error('Error fetching subscription plans:', err);
      setError(err.response?.data?.message || 'Failed to fetch subscription plans');
    } finally {
      setLoading(false);
    }
  };
  
  const handleChangePlan = async (planId) => {
    if (!isAdmin) return;
    
    if (!window.confirm('Are you sure you want to change your subscription plan?')) {
      return;
    }
    
    try {
      setProcessingPlan(planId);
      setError('');
      
      await axios.post('/backend/api/subscription/change', { plan_id: planId });
      
      // Refresh user data to get updated subscription
      await refreshUserData();
      
      // Show success message or redirect
    } catch (err) {
      console.error('Error changing subscription plan:', err);
      setError(err.response?.data?.message || 'Failed to change subscription plan');
    } finally {
      setProcessingPlan(null);
    }
  };
  
  // Define plan features
  const planFeatures = {
    users: {
      free: '2 users',
      basic: '5 users',
      pro: '15 users'
    },
    platforms: {
      free: 'Google My Business only',
      basic: 'Google My Business, Yelp',
      pro: 'Google My Business, Yelp, Facebook'
    },
    responses: {
      free: '50 AI responses/month',
      basic: '200 AI responses/month',
      pro: 'Unlimited AI responses'
    },
    analytics: {
      free: 'Basic analytics',
      basic: 'Advanced analytics',
      pro: 'Advanced analytics + custom reports'
    },
    support: {
      free: 'Email support',
      basic: 'Email + chat support',
      pro: 'Priority support'
    }
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Subscription Plans</h1>
      </div>
      
      {/* Current Plan Info */}
      <div className="bg-white shadow rounded-lg p-6 mb-6">
        <h2 className="text-lg font-medium text-gray-900 mb-2">Current Plan</h2>
        <div className="flex items-center">
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
            business?.subscription_plan === 'free' ? 'bg-gray-100 text-gray-800' :
            business?.subscription_plan === 'basic' ? 'bg-blue-100 text-blue-800' :
            'bg-purple-100 text-purple-800'
          }`}>
            {business?.subscription_plan === 'free' ? 'Free' :
             business?.subscription_plan === 'basic' ? 'Basic' : 'Pro'} Plan
          </span>
          {business?.subscription_trial_ends && (
            <span className="ml-2 text-sm text-gray-500">
              Trial ends on {new Date(business?.subscription_trial_ends).toLocaleDateString()}
            </span>
          )}
        </div>
        {!isAdmin && (
          <p className="mt-2 text-sm text-gray-500">
            Contact your administrator to change subscription plans.
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
      
      {/* Subscription Plans */}
      {loading ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {/* Free Plan */}
          <div className={`bg-white shadow rounded-lg overflow-hidden border-t-4 ${
            business?.subscription_plan === 'free' ? 'border-primary-500' : 'border-transparent'
          }`}>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-2">Free Plan</h3>
              <p className="text-3xl font-bold text-gray-900 mb-4">$0<span className="text-sm font-normal text-gray-500">/month</span></p>
              <p className="text-sm text-gray-500 mb-6">Perfect for small businesses just getting started.</p>
              
              <ul className="space-y-3 mb-6">
                {Object.entries(planFeatures).map(([feature, values]) => (
                  <li key={feature} className="flex items-start">
                    <CheckIcon className="h-5 w-5 text-green-500 mr-2 flex-shrink-0" />
                    <span className="text-sm text-gray-500">{values.free}</span>
                  </li>
                ))}
              </ul>
              
              {isAdmin && business?.subscription_plan !== 'free' && (
                <button
                  onClick={() => handleChangePlan('free')}
                  disabled={processingPlan === 'free'}
                  className="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                >
                  {processingPlan === 'free' ? 'Processing...' : 'Downgrade to Free'}
                </button>
              )}
              
              {business?.subscription_plan === 'free' && (
                <div className="w-full inline-flex justify-center rounded-md border border-primary-500 shadow-sm px-4 py-2 bg-primary-50 text-base font-medium text-primary-700">
                  Current Plan
                </div>
              )}
            </div>
          </div>
          
          {/* Basic Plan */}
          <div className={`bg-white shadow rounded-lg overflow-hidden border-t-4 ${
            business?.subscription_plan === 'basic' ? 'border-primary-500' : 'border-transparent'
          }`}>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-2">Basic Plan</h3>
              <p className="text-3xl font-bold text-gray-900 mb-4">$29<span className="text-sm font-normal text-gray-500">/month</span></p>
              <p className="text-sm text-gray-500 mb-6">Great for growing businesses with multiple review platforms.</p>
              
              <ul className="space-y-3 mb-6">
                {Object.entries(planFeatures).map(([feature, values]) => (
                  <li key={feature} className="flex items-start">
                    <CheckIcon className="h-5 w-5 text-green-500 mr-2 flex-shrink-0" />
                    <span className="text-sm text-gray-500">{values.basic}</span>
                  </li>
                ))}
              </ul>
              
              {isAdmin && business?.subscription_plan !== 'basic' && (
                <button
                  onClick={() => handleChangePlan('basic')}
                  disabled={processingPlan === 'basic'}
                  className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                >
                  {processingPlan === 'basic' ? 'Processing...' : business?.subscription_plan === 'free' ? 'Upgrade to Basic' : 'Downgrade to Basic'}
                </button>
              )}
              
              {business?.subscription_plan === 'basic' && (
                <div className="w-full inline-flex justify-center rounded-md border border-primary-500 shadow-sm px-4 py-2 bg-primary-50 text-base font-medium text-primary-700">
                  Current Plan
                </div>
              )}
            </div>
          </div>
          
          {/* Pro Plan */}
          <div className={`bg-white shadow rounded-lg overflow-hidden border-t-4 ${
            business?.subscription_plan === 'pro' ? 'border-primary-500' : 'border-transparent'
          }`}>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-2">Pro Plan</h3>
              <p className="text-3xl font-bold text-gray-900 mb-4">$79<span className="text-sm font-normal text-gray-500">/month</span></p>
              <p className="text-sm text-gray-500 mb-6">For businesses that need the full suite of features and support.</p>
              
              <ul className="space-y-3 mb-6">
                {Object.entries(planFeatures).map(([feature, values]) => (
                  <li key={feature} className="flex items-start">
                    <CheckIcon className="h-5 w-5 text-green-500 mr-2 flex-shrink-0" />
                    <span className="text-sm text-gray-500">{values.pro}</span>
                  </li>
                ))}
              </ul>
              
              {isAdmin && business?.subscription_plan !== 'pro' && (
                <button
                  onClick={() => handleChangePlan('pro')}
                  disabled={processingPlan === 'pro'}
                  className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                >
                  {processingPlan === 'pro' ? 'Processing...' : 'Upgrade to Pro'}
                </button>
              )}
              
              {business?.subscription_plan === 'pro' && (
                <div className="w-full inline-flex justify-center rounded-md border border-primary-500 shadow-sm px-4 py-2 bg-primary-50 text-base font-medium text-primary-700">
                  Current Plan
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
