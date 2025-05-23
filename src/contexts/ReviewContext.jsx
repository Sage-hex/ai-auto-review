import { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from './AuthContext';

const ReviewContext = createContext();

export function useReviews() {
  return useContext(ReviewContext);
}

export function ReviewProvider({ children }) {
  const { currentUser, business } = useAuth();
  const [reviews, setReviews] = useState([]);
  const [reviewStats, setReviewStats] = useState(null);
  const [pendingResponses, setPendingResponses] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [filters, setFilters] = useState({
    platform: '',
    rating: '',
    sentiment: ''
  });
  const [pagination, setPagination] = useState({
    currentPage: 1,
    totalPages: 1,
    totalReviews: 0
  });

  // Fetch reviews when user is logged in or filters change
  useEffect(() => {
    if (currentUser) {
      fetchReviews(1);
      fetchReviewStats();
      fetchPendingResponses();
    }
  }, [currentUser, filters]);

  // Fetch reviews
  const fetchReviews = async (page = 1) => {
    try {
      setLoading(true);
      setError('');
      
      // Build query parameters
      const params = new URLSearchParams();
      params.append('page', page);
      
      if (filters.platform) params.append('platform', filters.platform);
      if (filters.rating) params.append('rating', filters.rating);
      if (filters.sentiment) params.append('sentiment', filters.sentiment);
      
      const response = await axios.get(`/backend/api/reviews?${params.toString()}`);
      
      setReviews(response.data.data.reviews);
      setPagination({
        currentPage: response.data.data.pagination.current_page,
        totalPages: response.data.data.pagination.total_pages,
        totalReviews: response.data.data.pagination.total
      });
      
      return response.data.data;
    } catch (err) {
      console.error('Error fetching reviews:', err);
      setError(err.response?.data?.message || 'Failed to fetch reviews');
    } finally {
      setLoading(false);
    }
  };

  // Fetch review statistics
  const fetchReviewStats = async () => {
    try {
      const response = await axios.get('/backend/api/reviews/stats');
      setReviewStats(response.data.data);
      return response.data.data;
    } catch (err) {
      console.error('Error fetching review stats:', err);
    }
  };

  // Fetch pending responses
  const fetchPendingResponses = async () => {
    try {
      const response = await axios.get('/backend/api/responses/pending');
      setPendingResponses(response.data.data);
      return response.data.data;
    } catch (err) {
      console.error('Error fetching pending responses:', err);
    }
  };

  // Sync reviews from platforms
  const syncReviews = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.post('/backend/api/reviews/sync');
      
      // Refresh reviews and stats
      await fetchReviews(1);
      await fetchReviewStats();
      
      return response.data.data;
    } catch (err) {
      console.error('Error syncing reviews:', err);
      setError(err.response?.data?.message || 'Failed to sync reviews');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Generate AI response for a review
  const generateResponse = async (reviewId, businessName, businessType, tone) => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.post(`/backend/api/reviews/${reviewId}/generate`, {
        business_name: businessName || business.name,
        business_type: businessType || '',
        tone: tone || ''
      });
      
      // Refresh reviews
      await fetchReviews(pagination.currentPage);
      await fetchPendingResponses();
      
      return response.data.data;
    } catch (err) {
      console.error('Error generating response:', err);
      setError(err.response?.data?.message || 'Failed to generate response');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Update response
  const updateResponse = async (responseId, responseText) => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.put(`/backend/api/responses/${responseId}`, {
        response_text: responseText
      });
      
      // Refresh pending responses
      await fetchPendingResponses();
      
      return response.data.data;
    } catch (err) {
      console.error('Error updating response:', err);
      setError(err.response?.data?.message || 'Failed to update response');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Approve response
  const approveResponse = async (responseId) => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.post(`/backend/api/responses/${responseId}/approve`);
      
      // Refresh pending responses
      await fetchPendingResponses();
      
      return response.data.data;
    } catch (err) {
      console.error('Error approving response:', err);
      setError(err.response?.data?.message || 'Failed to approve response');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Post response to platform
  const postResponse = async (responseId) => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.post(`/backend/api/responses/${responseId}/post`);
      
      // Refresh reviews
      await fetchReviews(pagination.currentPage);
      
      return response.data.data;
    } catch (err) {
      console.error('Error posting response:', err);
      setError(err.response?.data?.message || 'Failed to post response');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Update filters
  const updateFilters = (newFilters) => {
    setFilters({ ...filters, ...newFilters });
  };

  const value = {
    reviews,
    reviewStats,
    pendingResponses,
    loading,
    error,
    filters,
    pagination,
    fetchReviews,
    fetchReviewStats,
    fetchPendingResponses,
    syncReviews,
    generateResponse,
    updateResponse,
    approveResponse,
    postResponse,
    updateFilters
  };

  return (
    <ReviewContext.Provider value={value}>
      {children}
    </ReviewContext.Provider>
  );
}
