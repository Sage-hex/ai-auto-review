import { useState, useEffect } from 'react';
import axios from 'axios';
import { XIcon } from '@heroicons/react/solid';

export default function ResponseForm({ review, response, onSave, onCancel }) {
  const [content, setContent] = useState('');
  const [status, setStatus] = useState('draft');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  
  const isEditing = !!response;
  
  useEffect(() => {
    if (response) {
      setContent(response.content || '');
      setStatus(response.status || 'draft');
    }
  }, [response]);
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validation
    if (!content) {
      setError('Response content is required');
      return;
    }
    
    try {
      setLoading(true);
      setError('');
      
      const responseData = {
        content,
        status,
        review_id: review.id
      };
      
      let result;
      
      if (isEditing) {
        // Update existing response
        result = await axios.put(`/backend/api/reviews/${review.id}/response`, responseData);
      } else {
        // Create new response
        result = await axios.post(`/backend/api/reviews/${review.id}/response`, responseData);
      }
      
      onSave(result.data.data);
    } catch (err) {
      console.error('Error saving response:', err);
      setError(err.response?.data?.message || 'Failed to save response');
    } finally {
      setLoading(false);
    }
  };
  
  const handlePublish = async () => {
    setStatus('published');
    setTimeout(() => {
      document.getElementById('responseForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    }, 0);
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
                {isEditing ? 'Edit Response' : 'Add Response'}
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
            
            <div className="mb-4">
              <div className="flex items-center mb-2">
                <div className="font-medium text-gray-700">Review from {review.user_name}</div>
                <div className="ml-2 text-sm text-gray-500">({review.rating} stars)</div>
              </div>
              <div className="bg-gray-50 p-3 rounded-md text-sm text-gray-700">
                {review.content}
              </div>
            </div>
            
            <form id="responseForm" onSubmit={handleSubmit}>
              <div className="mb-4">
                <label htmlFor="content" className="block text-sm font-medium text-gray-700 mb-1">
                  Your Response
                </label>
                <textarea
                  id="content"
                  value={content}
                  onChange={(e) => setContent(e.target.value)}
                  rows={6}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  placeholder="Write your response here..."
                  required
                ></textarea>
              </div>
              
              <div className="mb-4">
                <label htmlFor="status" className="block text-sm font-medium text-gray-700 mb-1">
                  Status
                </label>
                <select
                  id="status"
                  value={status}
                  onChange={(e) => setStatus(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="rejected">Rejected</option>
                </select>
                <p className="mt-1 text-xs text-gray-500">
                  Draft: Save for later. Published: Visible to customers. Rejected: Marked as inappropriate.
                </p>
              </div>
              
              <div className="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                  type="button"
                  onClick={handlePublish}
                  disabled={loading}
                  className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
                >
                  {loading ? 'Saving...' : 'Save & Publish'}
                </button>
                <button
                  type="submit"
                  disabled={loading}
                  className="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm disabled:opacity-50"
                >
                  {loading ? 'Saving...' : 'Save as Draft'}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
