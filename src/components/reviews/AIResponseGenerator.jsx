import { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import axios from 'axios';

export default function AIResponseGenerator({ review, onGenerated, onCancel }) {
  const { business } = useAuth();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [businessType, setBusinessType] = useState('');
  const [tone, setTone] = useState('professional');
  
  const handleGenerate = async () => {
    try {
      setLoading(true);
      setError('');
      
      // Prepare data for the AI response generation
      const data = {
        review_id: review.id,
        business_name: business?.name || '',
        business_type: businessType,
        tone: tone
      };
      
      // Make API request to generate response
      const response = await axios.post(`/backend/api/endpoints/reviews/generate-response.php`, data);
      
      // Call the onGenerated callback with the generated response
      onGenerated(response.data.data);
    } catch (err) {
      console.error('Error generating AI response:', err);
      setError(err.response?.data?.message || 'Failed to generate AI response');
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <div className="bg-white shadow-lg rounded-lg p-6 border border-gray-200">
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center">
          <svg className="w-6 h-6 text-primary-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
          </svg>
          <h2 className="text-xl font-bold text-gray-900">AI Response Generator</h2>
        </div>
        <div>
          <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            Powered by Google Gemini
          </span>
        </div>
      </div>
      
      {error && (
        <div className="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
          <div className="flex">
            <div className="ml-3">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          </div>
        </div>
      )}
      
      <div className="mb-6">
        <div className="bg-gray-50 p-4 rounded-lg mb-4">
          <div className="flex items-center mb-2">
            <div className="font-medium text-gray-700">Review from {review.user_name}</div>
            <div className="ml-2 text-sm text-gray-500">({review.rating} stars)</div>
          </div>
          <p className="text-gray-700">{review.content}</p>
        </div>
      </div>
      
      <div className="space-y-4">
        <div>
          <label htmlFor="businessType" className="block text-sm font-medium text-gray-700 mb-1">
            Business Type
          </label>
          <input
            type="text"
            id="businessType"
            value={businessType}
            onChange={(e) => setBusinessType(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            placeholder="e.g., Restaurant, Hotel, Retail Store"
          />
          <p className="mt-1 text-xs text-gray-500">
            Helps the AI understand your business context better
          </p>
        </div>
        
        <div>
          <label htmlFor="tone" className="block text-sm font-medium text-gray-700 mb-1">
            Response Tone
          </label>
          <select
            id="tone"
            value={tone}
            onChange={(e) => setTone(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="professional">Professional</option>
            <option value="friendly">Friendly</option>
            <option value="apologetic">Apologetic</option>
            <option value="enthusiastic">Enthusiastic</option>
            <option value="formal">Formal</option>
          </select>
        </div>
        
        <div className="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            onClick={onCancel}
            className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Cancel
          </button>
          <button
            type="button"
            onClick={handleGenerate}
            disabled={loading}
            className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
          >
            {loading ? (
              <>
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
              </>
            ) : (
              <>
                <svg className="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Generate AI Response
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
}
