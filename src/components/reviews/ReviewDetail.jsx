// import { useState, useEffect } from 'react';
// import { useParams, useNavigate } from 'react-router-dom';
// import { useReviews } from '../../contexts/ReviewContext';
// import { useAuth } from '../../contexts/AuthContext';
// import axios from 'axios';
// import { 
//   StarIcon, 
//   ArrowLeftIcon, 
//   RefreshIcon,
//   CheckCircleIcon,
//   XCircleIcon,
//   PencilIcon,
//   LightningBoltIcon
// } from '@heroicons/react/solid';
// import ResponseForm from '../responses/ResponseForm';
// import AIResponseGenerator from '../responses/AIResponseGenerator';

// export default function ReviewDetail() {
//   const { id } = useParams();
//   const navigate = useNavigate();
//   const { currentUser } = useAuth();
//   const [review, setReview] = useState(null);
//   const [response, setResponse] = useState(null);
//   const [responseHistory, setResponseHistory] = useState([]);
//   const [loading, setLoading] = useState(false);
//   const [error, setError] = useState('');
//   const [showResponseForm, setShowResponseForm] = useState(false);
//   const [showAIGenerator, setShowAIGenerator] = useState(false);
  
//   // Check if user has permission to respond
//   const canRespond = ['admin', 'manager', 'support'].includes(currentUser?.role);
  
//   useEffect(() => {
//     fetchReviewDetail();
//   }, [id]);
  
//   const fetchReviewDetail = async () => {
//     try {
//       setLoading(true);
//       setError('');
      
//       const reviewResponse = await axios.get(`/backend/api/reviews/${id}`);
//       setReview(reviewResponse.data.data.review);
      
//       // Get responses for this review
//       const responses = reviewResponse.data.data.responses || [];
      
//       // Set response history
//       setResponseHistory(responses);
      
//       // Set current response (most recent one)
//       if (responses.length > 0) {
//         setResponse(responses[0]);
//       }
//     } catch (err) {
//       console.error('Error fetching review details:', err);
//       setError(err.response?.data?.message || 'Failed to fetch review details');
//     } finally {
//       setLoading(false);
//     }
//   };
  
//   const handleGenerateResponse = async () => {
//     // Show AI response generator
//     setShowAIGenerator(true);
//   };
  
//   const handleAIResponseGenerated = (generatedResponse) => {
//     // Close AI generator
//     setShowAIGenerator(false);
    
//     // Set the generated response
//     setResponse(generatedResponse);
    
//     // Add to response history if not already there
//     if (!responseHistory.find(r => r.id === generatedResponse.id)) {
//       setResponseHistory([generatedResponse, ...responseHistory]);
//     }
    
//     // Show response form with generated content for editing
//     setShowResponseForm(true);
//   };
  
//   const handleResponseSaved = (savedResponse) => {
//     setResponse(savedResponse);
//     setShowResponseForm(false);
//     fetchReviewDetail(); // Refresh the review data
//   };
  
//   const handleEditResponse = () => {
//     setShowResponseForm(true);
//   };
  
//   const handleGoBack = () => {
//     navigate('/reviews');
//   };
  
//   // Function to render stars based on rating
//   const renderStars = (rating) => {
//     return Array(5)
//       .fill()
//       .map((_, i) => (
//         <StarIcon
//           key={i}
//           className={`h-5 w-5 ${
//             i < rating ? 'text-yellow-400' : 'text-gray-300'
//           }`}
//         />
//       ));
//   };
  
//   // Function to get sentiment color
//   const getSentimentColor = (sentiment) => {
//     switch (sentiment) {
//       case 'positive':
//         return 'bg-green-100 text-green-800';
//       case 'negative':
//         return 'bg-red-100 text-red-800';
//       default:
//         return 'bg-gray-100 text-gray-800';
//     }
//   };
  
//   // Function to get platform badge color
//   const getPlatformColor = (platform) => {
//     switch (platform) {
//       case 'google':
//         return 'bg-blue-100 text-blue-800';
//       case 'yelp':
//         return 'bg-red-100 text-red-800';
//       case 'facebook':
//         return 'bg-indigo-100 text-indigo-800';
//       default:
//         return 'bg-gray-100 text-gray-800';
//     }
//   };
  
//   // Function to get response status color
//   const getResponseStatusColor = (status) => {
//     switch (status) {
//       case 'published':
//         return 'bg-green-100 text-green-800';
//       case 'draft':
//         return 'bg-yellow-100 text-yellow-800';
//       case 'rejected':
//         return 'bg-red-100 text-red-800';
//       default:
//         return 'bg-gray-100 text-gray-800';
//     }
//   };

//   return (
//     <div>
//       <div className="flex items-center mb-6">
//         <button
//           onClick={handleGoBack}
//           className="mr-4 text-gray-500 hover:text-gray-700"
//         >
//           <ArrowLeftIcon className="h-5 w-5" />
//         </button>
//         <h1 className="text-2xl font-bold text-gray-900">Review Details</h1>
//       </div>
      
//       {/* Error Message */}
//       {error && (
//         <div className="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
//           <div className="flex">
//             <div className="ml-3">
//               <p className="text-sm text-red-700">{error}</p>
//             </div>
//           </div>
//         </div>
//       )}
      
//       {loading && !review ? (
//         <div className="flex justify-center py-12">
//           <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
//         </div>
//       ) : review ? (
//         <div className="space-y-6">
//           {/* Review Card */}
//           <div className="bg-white shadow rounded-lg overflow-hidden">
//             <div className="p-6">
//               <div className="flex items-center justify-between mb-4">
//                 <div>
//                   <h2 className="text-lg font-medium text-gray-900">{review.user_name}</h2>
//                   <div className="flex items-center mt-1">
//                     <div className="flex mr-2">
//                       {renderStars(review.rating)}
//                     </div>
//                     <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPlatformColor(review.platform)}`}>
//                       {review.platform}
//                     </span>
//                     {review.sentiment && (
//                       <span className={`ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getSentimentColor(review.sentiment)}`}>
//                         {review.sentiment}
//                       </span>
//                     )}
//                   </div>
//                 </div>
//                 <div className="text-sm text-gray-500">
//                   {new Date(review.created_at).toLocaleDateString()}
//                 </div>
//               </div>
              
//               <div className="prose max-w-none mb-4">
//                 <p className="text-gray-700">{review.content}</p>
//               </div>
              
//               <div className="flex items-center text-sm text-gray-500">
//                 <span className="mr-2">Review ID:</span>
//                 <span className="font-mono">{review.id}</span>
//               </div>
              
//               {review.external_id && (
//                 <div className="flex items-center text-sm text-gray-500 mt-1">
//                   <span className="mr-2">External ID:</span>
//                   <span className="font-mono">{review.external_id}</span>
//                 </div>
//               )}
//             </div>
//           </div>
          
//           {/* Response Section */}
//           <div className="bg-white shadow rounded-lg overflow-hidden">
//             <div className="px-6 py-4 border-b border-gray-200">
//               <h3 className="text-lg font-medium text-gray-900">Response</h3>
//             </div>
            
//             <div className="p-6">
//               {response ? (
//                 <div>
//                   <div className="flex items-center justify-between mb-4">
//                     <div className="flex items-center">
//                       <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getResponseStatusColor(response.status)}`}>
//                         {response.status}
//                       </span>
//                       {response.is_ai_generated && (
//                         <span className="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
//                           AI Generated
//                         </span>
//                       )}
//                     </div>
                    
//                     {canRespond && (
//                       <button
//                         onClick={handleEditResponse}
//                         className="inline-flex items-center text-sm text-primary-600 hover:text-primary-700"
//                       >
//                         <PencilIcon className="h-4 w-4 mr-1" />
//                         Edit Response
//                       </button>
//                     )}
//                   </div>
                  
//                   <div className="prose max-w-none">
//                     <p className="text-gray-700">{response.response_text}</p>
//                   </div>
                  
//                   {response.responded_at && (
//                     <div className="mt-4 text-sm text-gray-500">
//                       Responded on {new Date(response.responded_at).toLocaleDateString()}
//                     </div>
//                   )}
//                 </div>
//               ) : (
//         <div className="bg-white shadow rounded-lg p-6 text-center">
//           <p className="text-gray-500">Review not found.</p>
//           <button
//             onClick={handleGoBack}
//             className="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
//           >
//             Back to Reviews
//           </button>
//         </div>
//       )}
      
//       {/* Response Form */}
//       {showResponseForm && (
//         <ResponseForm
//           review={review}
//           response={response}
//           onSave={handleResponseSaved}
//           onCancel={() => setShowResponseForm(false)}
//         />
//       )}
      
//       {/* AI Response Generator */}
//       {showAIGenerator && (
//         <AIResponseGenerator
//           review={review}
//           onGenerated={handleAIResponseGenerated}
//           onCancel={() => setShowAIGenerator(false)}
//         />
//       )}
      
//       {/* Response History */}
//       {responseHistory.length > 1 && (
//         <div className="mt-8">
//           <h3 className="text-lg font-medium text-gray-900 mb-4">Response History</h3>
//           <div className="bg-white shadow overflow-hidden sm:rounded-md">
//             <ul className="divide-y divide-gray-200">
//               {responseHistory.map((historyItem) => (
//                 <li key={historyItem.id} className="px-4 py-4">
//                   <div className="flex items-center justify-between">
//                     <div className="flex items-center">
//                       <div className={`flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center ${
//                         historyItem.status === 'approved' ? 'bg-green-100' : 
//                         historyItem.status === 'pending' ? 'bg-yellow-100' : 'bg-gray-100'
//                       }`}>
//                         {historyItem.status === 'approved' ? (
//                           <CheckCircleIcon className="h-6 w-6 text-green-600" />
//                         ) : historyItem.status === 'pending' ? (
//                           <svg className="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
//                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
//                           </svg>
//                         ) : (
//                           <XCircleIcon className="h-6 w-6 text-gray-600" />
//                         )}
//                       </div>
//                       <div className="ml-4">
//                         <div className="text-sm font-medium text-gray-900">
//                           {historyItem.status === 'approved' ? 'Approved Response' : 
//                            historyItem.status === 'pending' ? 'Pending Response' : 'Draft Response'}
//                         </div>
//                         <div className="text-sm text-gray-500">
//                           {new Date(historyItem.created_at).toLocaleString()}
//                           {historyItem.is_ai_generated && (
//                             <span className="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
//                               AI Generated
//                             </span>
//                           )}
//                         </div>
//                       </div>
//                     </div>
//                     <div className="ml-2 flex-shrink-0 flex">
//                       {historyItem.id !== response?.id && (
//                         <button
//                           onClick={() => setResponse(historyItem)}
//                           className="px-2 py-1 text-xs font-medium text-primary-600 hover:text-primary-900"
//                         >
//                           View
//                         </button>
//                       )}
//                     </div>
//                   </div>
//                   {historyItem.id === response?.id && (
//                     <div className="mt-2 text-sm text-gray-700 bg-gray-50 p-3 rounded">
//                       {historyItem.response_text}
//                     </div>
//                   )}
//                 </li>
//               ))}
//             </ul>
//           </div>
//         </div>
//       )}
//     </div>
//   );
// }


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
  PencilIcon,
  LightningBoltIcon
} from '@heroicons/react/solid';
import ResponseForm from '../responses/ResponseForm';
import AIResponseGenerator from '../responses/AIResponseGenerator';

export default function ReviewDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { currentUser } = useAuth();
  const [review, setReview] = useState(null);
  const [response, setResponse] = useState(null);
  const [responseHistory, setResponseHistory] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showResponseForm, setShowResponseForm] = useState(false);
  const [showAIGenerator, setShowAIGenerator] = useState(false);
  
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
      setReview(reviewResponse.data.data.review);
      
      // Get responses for this review
      const responses = reviewResponse.data.data.responses || [];
      
      // Set response history
      setResponseHistory(responses);
      
      // Set current response (most recent one)
      if (responses.length > 0) {
        setResponse(responses[0]);
      }
    } catch (err) {
      console.error('Error fetching review details:', err);
      setError(err.response?.data?.message || 'Failed to fetch review details');
    } finally {
      setLoading(false);
    }
  };
  
  const handleGenerateResponse = async () => {
    // Show AI response generator
    setShowAIGenerator(true);
  };
  
  const handleAIResponseGenerated = (generatedResponse) => {
    // Close AI generator
    setShowAIGenerator(false);
    
    // Set the generated response
    setResponse(generatedResponse);
    
    // Add to response history if not already there
    if (!responseHistory.find(r => r.id === generatedResponse.id)) {
      setResponseHistory([generatedResponse, ...responseHistory]);
    }
    
    // Show response form with generated content for editing
    setShowResponseForm(true);
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
                    <p className="text-gray-700">{response.response_text}</p>
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
                </div>
              )}
              
              {/* Response Actions */}
              {canRespond && (
                <div className="mt-6 flex flex-col sm:flex-row sm:justify-end gap-3">
                  {response ? (
                    <>
                      <button
                        onClick={handleEditResponse}
                        className="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                      >
                        <PencilIcon className="-ml-1 mr-2 h-5 w-5 text-gray-500" />
                        Edit Response
                      </button>
                      <button
                        onClick={handleGenerateResponse}
                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                      >
                        <LightningBoltIcon className="-ml-1 mr-2 h-5 w-5" />
                        Try AI Response
                      </button>
                    </>
                  ) : (
                    <>
                      <button
                        onClick={() => setShowResponseForm(true)}
                        className="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                      >
                        <PencilIcon className="-ml-1 mr-2 h-5 w-5 text-gray-500" />
                        Write Response
                      </button>
                      <button
                        onClick={handleGenerateResponse}
                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                      >
                        <LightningBoltIcon className="-ml-1 mr-2 h-5 w-5" />
                        Generate AI Response
                      </button>
                    </>
                  )}
                </div>
              )}
            </div>
          </div>
          
          {/* Response Form */}
          {showResponseForm && (
            <ResponseForm
              review={review}
              response={response}
              onSave={handleResponseSaved}
              onCancel={() => setShowResponseForm(false)}
            />
          )}
          
          {/* AI Response Generator */}
          {showAIGenerator && (
            <AIResponseGenerator
              review={review}
              onGenerated={handleAIResponseGenerated}
              onCancel={() => setShowAIGenerator(false)}
            />
          )}
          
          {/* Response History */}
          {responseHistory.length > 1 && (
            <div className="mt-8">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Response History</h3>
              <div className="bg-white shadow overflow-hidden sm:rounded-md">
                <ul className="divide-y divide-gray-200">
                  {responseHistory.map((historyItem) => (
                    <li key={historyItem.id} className="px-4 py-4">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center">
                          <div className={`flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center ${
                            historyItem.status === 'approved' ? 'bg-green-100' : 
                            historyItem.status === 'pending' ? 'bg-yellow-100' : 'bg-gray-100'
                          }`}>
                            {historyItem.status === 'approved' ? (
                              <CheckCircleIcon className="h-6 w-6 text-green-600" />
                            ) : historyItem.status === 'pending' ? (
                              <svg className="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                              </svg>
                            ) : (
                              <XCircleIcon className="h-6 w-6 text-gray-600" />
                            )}
                          </div>
                          <div className="ml-4">
                            <div className="text-sm font-medium text-gray-900">
                              {historyItem.status === 'approved' ? 'Approved Response' : 
                               historyItem.status === 'pending' ? 'Pending Response' : 'Draft Response'}
                            </div>
                            <div className="text-sm text-gray-500">
                              {new Date(historyItem.created_at).toLocaleString()}
                              {historyItem.is_ai_generated && (
                                <span className="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                  AI Generated
                                </span>
                              )}
                            </div>
                          </div>
                        </div>
                        <div className="ml-2 flex-shrink-0 flex">
                          {historyItem.id !== response?.id && (
                            <button
                              onClick={() => setResponse(historyItem)}
                              className="px-2 py-1 text-xs font-medium text-primary-600 hover:text-primary-900"
                            >
                              View
                            </button>
                          )}
                        </div>
                      </div>
                      {historyItem.id === response?.id && (
                        <div className="mt-2 text-sm text-gray-700 bg-gray-50 p-3 rounded">
                          {historyItem.response_text}
                        </div>
                      )}
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          )}
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
    </div>
  );
}
