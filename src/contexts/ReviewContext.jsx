import { createContext, useContext, useState, useEffect } from 'react';
import { useAuth } from './AuthContext';
import api from '../utils/api';

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
    sentiment: '',
    response_status: '',
  });
  const [pagination, setPagination] = useState({ currentPage: 1, totalPages: 1, totalReviews: 0 });

  useEffect(() => {
    if (!currentUser) return;
    fetchReviews(1);
    fetchReviewStats();
    fetchPendingResponses();
  }, [currentUser, filters]);

  const fetchReviews = async (page = 1) => {
    try {
      setLoading(true);
      setError('');

      const params = new URLSearchParams({ page: String(page) });
      Object.entries(filters).forEach(([key, value]) => {
        if (value) params.append(key, value);
      });

      const response = await api.get(`/reviews?${params.toString()}`);
      setReviews(response.data.data.reviews);
      setPagination({
        currentPage: response.data.data.pagination.current_page,
        totalPages: response.data.data.pagination.total_pages,
        totalReviews: response.data.data.pagination.total,
      });
      return response.data.data;
    } catch (err) {
      setError(err.response?.data?.detail || err.response?.data?.message || 'Failed to fetch reviews');
    } finally {
      setLoading(false);
    }
  };

  const fetchReviewStats = async () => {
    try {
      const response = await api.get('/reviews/stats');
      setReviewStats(response.data.data);
      return response.data.data;
    } catch {
      return null;
    }
  };

  const fetchPendingResponses = async () => {
    try {
      const response = await api.get('/responses/pending');
      setPendingResponses(response.data.data);
      return response.data.data;
    } catch {
      return [];
    }
  };

  const syncReviews = async () => {
    const response = await api.post('/reviews/sync');
    await fetchReviews(1);
    await fetchReviewStats();
    return response.data.data;
  };

  const generateResponse = async (reviewId, businessName, businessType, tone) => {
    const response = await api.post(`/reviews/${reviewId}/generate`, {
      business_name: businessName || business?.name,
      business_type: businessType,
      tone,
    });
    await fetchReviews(pagination.currentPage);
    await fetchPendingResponses();
    return response.data.data;
  };

  const updateResponse = async (responseId, responseText) => {
    const response = await api.put(`/responses/${responseId}`, { response_text: responseText });
    await fetchPendingResponses();
    return response.data.data;
  };

  const approveResponse = async (responseId) => {
    const response = await api.post(`/responses/${responseId}/approve`);
    await fetchPendingResponses();
    return response.data.data;
  };

  const postResponse = async (responseId) => {
    const response = await api.post(`/responses/${responseId}/post`);
    await fetchReviews(pagination.currentPage);
    return response.data.data;
  };

  const updateFilters = (newFilters) => setFilters((prev) => ({ ...prev, ...newFilters }));

  return (
    <ReviewContext.Provider
      value={{
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
        updateFilters,
      }}
    >
      {children}
    </ReviewContext.Provider>
  );
}
