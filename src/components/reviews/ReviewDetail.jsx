import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useReviews } from '../../contexts/ReviewContext';
import { useAuth } from '../../contexts/AuthContext';
import axios from 'axios';
import { 
  StarIcon, 
  ArrowLeftIcon, 
  RefreshIcon,
  CheckCircleIcon,
  XCircleIcon,
  PencilIcon
} from '@heroicons/react/solid';
import ResponseForm from '../responses/ResponseForm';

export default function ReviewDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { currentUser } = useAuth();
  const [review, setReview] = useState(null);
  const [response, setResponse] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showResponseForm, setShowResponseForm] = useState(false);
  
  // Check if user has permission to respond
  const canRespond = ['admin', 'manager', 'support'].includes(currentUser?.role);
  
  useEffect(() => {
    fetchReviewDetail();
  }, [id]);
  
  const fetchReviewDetail = async () => {
    try {
      setLoading(true);
      setError('');
      
      const reviewResponse = await axios.get(`/backend/api/reviews/${id}`);
      setReview(reviewResponse.data.data);
      
      // Check if there's a response
      if (reviewResponse.data.data.has_response) {
        const responseData = await axios.get(`/backend/api/reviews/${id}/response`);
        setResponse(responseData.data.data);
      }
    } catch (err) {
      console.error('Error fetching review details:', err);
      setError(err.response?.data?.message || 'Failed to fetch review details');
    } finally {
      setLoading(false);
    }
  };
  
  const handleGenerateResponse = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.post(`/backend/api/reviews/${id}/generate-response`);
      
      // Open response form with generated content
      setResponse({
        content: response.data.data.content,
        is_ai_generated: true,
        status: 'draft'
      });
      
      setShowResponseForm(true);
    } catch (err) {
      console.error('Error generating response:', err);
      setError(err.response?.data?.message || 'Failed to generate response');
    } finally {
      setLoading(false);
    }
  };
  
  const handleResponseSaved = (savedResponse) => {
    setResponse(savedResponse);
    setShowResponseForm(false);
    fetchReviewDetail(); // Refresh the review data
  };
  
  const handleEditResponse = () => {
    setShowResponseForm(true);
  };
  
  const handleGoBack = () => {
    navigate('/reviews');
  };
  
  // Function to render stars based on rating
  const renderStars = (rating) => {
    return Array(5)
      .fill()
      .map((_, i) => (
        <StarIcon
          key={i}
          className={`h-5 w-5 ${
            i < rating ? 'text-yellow-400' : 'text-gray-300'
          }`}
        />
      ));
  };
  
  // Function to get sentiment color
  const getSentimentColor = (sentiment) => {
    switch (sentiment) {
      case 'positive':
        return 'bg-green-100 text-green-800';
      case 'negative':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };
  
  // Function to get platform badge color
  const getPlatformColor = (platform) => {
    switch (platform) {
      case 'google':
        return 'bg-blue-100 text-blue-800';
      case 'yelp':
        return 'bg-red-100 text-red-800';
      case 'facebook':
        return 'bg-indigo-100 text-indigo-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };
  
  // Function to get response status color
  const getResponseStatusColor = (status) => {
    switch (status) {
      case 'published':
        return 'bg-green-100 text-green-800';
      case 'draft':
        return 'bg-yellow-100 text-yellow-800';
      case 'rejected':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div>
      <div className="flex items-center mb-6">
        <button
          onClick={handleGoBack}
          className="mr-4 text-gray-500 hover:text-gray-700"
        >
          <ArrowLeftIcon className="h-5 w-5" />
        </button>
        <h1 className="text-2xl font-bold text-gray-900">Review Details</h1>
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
      
      {loading && !review ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
        </div>
      ) : review ? (
        <div className="space-y-6">
          {/* Review Card */}
          <div className="bg-white shadow rounded-lg overflow-hidden">
            <div className="p-6">
              <div className="flex items-center justify-between mb-4">
                <div>
                  <h2 className="text-lg font-medium text-gray-900">{review.user_name}</h2>
                  <div className="flex items-center mt-1">
                    <div className="flex mr-2">
                      {renderStars(review.rating)}
                    </div>
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPlatformColor(review.platform)}`}>
                      {review.platform}
                    </span>
                    {review.sentiment && (
                      <span className={`ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getSentimentColor(review.sentiment)}`}>
                        {review.sentiment}
                      </span>
                    )}
                  </div>
                </div>
                <div className="text-sm text-gray-500">
                  {new Date(review.created_at).toLocaleDateString()}
                </div>
              </div>
              
              <div className="prose max-w-none mb-4">
                <p className="text-gray-700">{review.content}</p>
              </div>
              
              <div className="flex items-center text-sm text-gray-500">
                <span className="mr-2">Review ID:</span>
                <span className="font-mono">{review.id}</span>
              </div>
              
              {review.external_id && (
                <div className="flex items-center text-sm text-gray-500 mt-1">
                  <span className="mr-2">External ID:</span>
                  <span className="font-mono">{review.external_id}</span>
                </div>
              )}
            </div>
          </div>
          
          {/* Response Section */}
          <div className="bg-white shadow rounded-lg overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200">
              <h3 className="text-lg font-medium text-gray-900">Response</h3>
            </div>
            
            <div className="p-6">
              {response ? (
                <div>
                  <div className="flex items-center justify-between mb-4">
                    <div className="flex items-center">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getResponseStatusColor(response.status)}`}>
                        {response.status}
                      </span>
                      {response.is_ai_generated && (
                        <span className="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                          AI Generated
                        </span>
                      )}
                    </div>
                    
                    {canRespond && (
                      <button
                        onClick={handleEditResponse}
                        className="inline-flex items-center text-sm text-primary-600 hover:text-primary-700"
                      >
                        <PencilIcon className="h-4 w-4 mr-1" />
                        Edit Response
                      </button>
                    )}
                  </div>
                  
                  <div className="prose max-w-none">
                    <p className="text-gray-700">{response.content}</p>
                  </div>
                  
                  {response.responded_at && (
                    <div className="mt-4 text-sm text-gray-500">
                      Responded on {new Date(response.responded_at).toLocaleDateString()}
                    </div>
                  )}
                </div>
              ) : (
                <div>
                  <p className="text-gray-500 mb-4">No response has been added yet.</p>
                  
                  {canRespond && (
                    <div className="flex space-x-4">
                      <button
                        onClick={() => setShowResponseForm(true)}
                        className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                      >
                        <PencilIcon className="h-4 w-4 mr-2" />
                        Write Response
                      </button>
                      
                      <button
                        onClick={handleGenerateResponse}
                        disabled={loading}
                        className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                      >
                        <RefreshIcon className="h-4 w-4 mr-2" />
                        {loading ? 'Generating...' : 'Generate with AI'}
                      </button>
                    </div>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>
      ) : (
        <div className="bg-white shadow rounded-lg p-6 text-center">
          <p className="text-gray-500">Review not found.</p>
          <button
            onClick={handleGoBack}
            className="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Back to Reviews
          </button>
        </div>
      )}
      
      {/* Response Form Modal */}
      {showResponseForm && (
        <ResponseForm
          review={review}
          response={response}
          onSave={handleResponseSaved}
          onCancel={() => setShowResponseForm(false)}
        />
      )}
    </div>
  );
}
