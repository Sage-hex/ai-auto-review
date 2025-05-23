import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useReviews } from '../../contexts/ReviewContext';
import { useAuth } from '../../contexts/AuthContext';
import ReviewList from './ReviewList';
import { 
  ChartPieIcon, 
  RefreshIcon, 
  FilterIcon,
  SortAscendingIcon,
  SortDescendingIcon,
  SearchIcon,
  ExclamationCircleIcon,
  CheckCircleIcon
} from '@heroicons/react/solid';

export default function ReviewManagement() {
  const navigate = useNavigate();
  const { currentUser, business } = useAuth();
  const { 
    reviews, 
    reviewStats, 
    loading, 
    error, 
    fetchReviews, 
    fetchReviewStats, 
    syncReviews,
    updateFilters,
    filters
  } = useReviews();
  
  const [showFilters, setShowFilters] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState('date');
  const [sortOrder, setSortOrder] = useState('desc');
  
  // Check if user has permission to manage reviews
  const canManageReviews = ['admin', 'manager', 'support'].includes(currentUser?.role);
  
  useEffect(() => {
    // Fetch reviews and stats on component mount
    fetchReviews(1);
    fetchReviewStats();
  }, []);
  
  const handleSyncReviews = async () => {
    try {
      await syncReviews();
    } catch (error) {
      console.error('Error syncing reviews:', error);
    }
  };
  
  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    updateFilters({ [name]: value });
  };
  
  const handleSearchChange = (e) => {
    setSearchTerm(e.target.value);
  };
  
  const handleSortChange = (sortField) => {
    if (sortBy === sortField) {
      // Toggle sort order if clicking the same field
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
    } else {
      // Set new sort field and default to descending
      setSortBy(sortField);
      setSortOrder('desc');
    }
  };
  
  const toggleFilters = () => {
    setShowFilters(!showFilters);
  };
  
  const handleViewReview = (reviewId) => {
    navigate(`/reviews/${reviewId}`);
  };
  
  return (
    <div className="bg-white shadow rounded-lg p-6">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Review Management</h1>
          <p className="text-gray-500 mt-1">
            Manage and respond to customer reviews across all platforms
          </p>
        </div>
        
        <div className="flex flex-col sm:flex-row gap-2 mt-4 md:mt-0">
          <button
            onClick={toggleFilters}
            className="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <FilterIcon className="h-5 w-5 mr-2" />
            Filters
          </button>
          
          {canManageReviews && (
            <button
              onClick={handleSyncReviews}
              disabled={loading}
              className="flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
            >
              <RefreshIcon className={`h-5 w-5 mr-2 ${loading ? 'animate-spin' : ''}`} />
              {loading ? 'Syncing...' : 'Sync Reviews'}
            </button>
          )}
        </div>
      </div>
      
      {/* Error Message */}
      {error && (
        <div className="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
          <div className="flex">
            <ExclamationCircleIcon className="h-5 w-5 text-red-400" />
            <div className="ml-3">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          </div>
        </div>
      )}
      
      {/* Review Stats */}
      {reviewStats && (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div className="bg-gray-50 p-4 rounded-lg">
            <div className="flex items-center">
              <div className="flex-shrink-0 bg-primary-100 rounded-md p-3">
                <ChartPieIcon className="h-6 w-6 text-primary-600" />
              </div>
              <div className="ml-4">
                <p className="text-sm font-medium text-gray-500">Total Reviews</p>
                <p className="text-2xl font-semibold text-gray-900">{reviewStats.total_reviews || 0}</p>
              </div>
            </div>
          </div>
          
          <div className="bg-gray-50 p-4 rounded-lg">
            <div className="flex items-center">
              <div className="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                <svg className="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                </svg>
              </div>
              <div className="ml-4">
                <p className="text-sm font-medium text-gray-500">Average Rating</p>
                <p className="text-2xl font-semibold text-gray-900">{reviewStats.average_rating?.toFixed(1) || 0}</p>
              </div>
            </div>
          </div>
          
          <div className="bg-gray-50 p-4 rounded-lg">
            <div className="flex items-center">
              <div className="flex-shrink-0 bg-green-100 rounded-md p-3">
                <CheckCircleIcon className="h-6 w-6 text-green-600" />
              </div>
              <div className="ml-4">
                <p className="text-sm font-medium text-gray-500">Responded</p>
                <p className="text-2xl font-semibold text-gray-900">
                  {reviewStats.responded_reviews || 0}
                  <span className="text-sm text-gray-500 ml-1">
                    ({Math.round(((reviewStats.responded_reviews || 0) / (reviewStats.total_reviews || 1)) * 100)}%)
                  </span>
                </p>
              </div>
            </div>
          </div>
          
          <div className="bg-gray-50 p-4 rounded-lg">
            <div className="flex items-center">
              <div className="flex-shrink-0 bg-blue-100 rounded-md p-3">
                <svg className="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm0 4c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm6 12H6v-1.4c0-2 4-3.1 6-3.1s6 1.1 6 3.1V19z" />
                </svg>
              </div>
              <div className="ml-4">
                <p className="text-sm font-medium text-gray-500">Pending Responses</p>
                <p className="text-2xl font-semibold text-gray-900">{reviewStats.pending_responses || 0}</p>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {/* Filters */}
      {showFilters && (
        <div className="bg-gray-50 p-6 rounded-lg mb-6">
          <h2 className="text-lg font-medium text-gray-900 mb-4">Filters & Search</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
              <label htmlFor="platform" className="block text-sm font-medium text-gray-700 mb-1">
                Platform
              </label>
              <select
                id="platform"
                name="platform"
                value={filters.platform}
                onChange={handleFilterChange}
                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">All Platforms</option>
                <option value="google">Google</option>
                <option value="yelp">Yelp</option>
                <option value="facebook">Facebook</option>
              </select>
            </div>
            
            <div>
              <label htmlFor="rating" className="block text-sm font-medium text-gray-700 mb-1">
                Rating
              </label>
              <select
                id="rating"
                name="rating"
                value={filters.rating}
                onChange={handleFilterChange}
                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">All Ratings</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
              </select>
            </div>
            
            <div>
              <label htmlFor="sentiment" className="block text-sm font-medium text-gray-700 mb-1">
                Sentiment
              </label>
              <select
                id="sentiment"
                name="sentiment"
                value={filters.sentiment}
                onChange={handleFilterChange}
                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">All Sentiments</option>
                <option value="positive">Positive</option>
                <option value="neutral">Neutral</option>
                <option value="negative">Negative</option>
              </select>
            </div>
            
            <div>
              <label htmlFor="response_status" className="block text-sm font-medium text-gray-700 mb-1">
                Response Status
              </label>
              <select
                id="response_status"
                name="response_status"
                value={filters.response_status}
                onChange={handleFilterChange}
                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">All Statuses</option>
                <option value="responded">Responded</option>
                <option value="not_responded">Not Responded</option>
                <option value="pending">Pending Approval</option>
              </select>
            </div>
          </div>
          
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <SearchIcon className="h-5 w-5 text-gray-400" />
            </div>
            <input
              type="text"
              name="search"
              id="search"
              value={searchTerm}
              onChange={handleSearchChange}
              className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="Search reviews by content or customer name"
            />
          </div>
        </div>
      )}
      
      {/* Reviews List */}
      <div className="bg-white shadow overflow-hidden sm:rounded-md">
        <div className="flex items-center justify-between bg-gray-50 px-4 py-3 border-b border-gray-200">
          <div className="text-sm font-medium text-gray-700">
            {loading ? 'Loading reviews...' : `${reviewStats?.total_reviews || 0} reviews`}
          </div>
          
          <div className="flex items-center space-x-2">
            <button
              onClick={() => handleSortChange('date')}
              className={`flex items-center px-3 py-1 text-sm rounded-md ${
                sortBy === 'date' ? 'bg-gray-200' : 'hover:bg-gray-100'
              }`}
            >
              Date
              {sortBy === 'date' && (
                sortOrder === 'desc' ? 
                <SortDescendingIcon className="h-4 w-4 ml-1" /> : 
                <SortAscendingIcon className="h-4 w-4 ml-1" />
              )}
            </button>
            
            <button
              onClick={() => handleSortChange('rating')}
              className={`flex items-center px-3 py-1 text-sm rounded-md ${
                sortBy === 'rating' ? 'bg-gray-200' : 'hover:bg-gray-100'
              }`}
            >
              Rating
              {sortBy === 'rating' && (
                sortOrder === 'desc' ? 
                <SortDescendingIcon className="h-4 w-4 ml-1" /> : 
                <SortAscendingIcon className="h-4 w-4 ml-1" />
              )}
            </button>
          </div>
        </div>
        
        <ReviewList 
          onViewReview={handleViewReview}
          sortBy={sortBy}
          sortOrder={sortOrder}
          searchTerm={searchTerm}
        />
      </div>
    </div>
  );
}
