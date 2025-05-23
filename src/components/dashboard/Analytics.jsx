import { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import axios from 'axios';
import { 
  ChartPieIcon, 
  ChartBarIcon, 
  TrendingUpIcon,
  TrendingDownIcon,
  StarIcon
} from '@heroicons/react/solid';

export default function Analytics() {
  const { business } = useAuth();
  const [stats, setStats] = useState({
    total_reviews: 0,
    average_rating: 0,
    rating_distribution: {
      1: 0,
      2: 0,
      3: 0,
      4: 0,
      5: 0
    },
    platform_distribution: {
      google: 0,
      yelp: 0,
      facebook: 0
    },
    sentiment_distribution: {
      positive: 0,
      neutral: 0,
      negative: 0
    },
    response_rate: 0,
    recent_trend: 'stable'
  });
  const [timeRange, setTimeRange] = useState('30days');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  
  useEffect(() => {
    fetchAnalytics();
  }, [timeRange]);
  
  const fetchAnalytics = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await axios.get(`/backend/api/analytics?timeRange=${timeRange}`);
      setStats(response.data.data);
    } catch (err) {
      console.error('Error fetching analytics:', err);
      setError(err.response?.data?.message || 'Failed to fetch analytics data');
    } finally {
      setLoading(false);
    }
  };
  
  const handleTimeRangeChange = (e) => {
    setTimeRange(e.target.value);
  };
  
  // Function to get trend icon and color
  const getTrendDisplay = (trend) => {
    switch (trend) {
      case 'up':
        return {
          icon: <TrendingUpIcon className="h-5 w-5 text-green-500" />,
          color: 'text-green-500'
        };
      case 'down':
        return {
          icon: <TrendingDownIcon className="h-5 w-5 text-red-500" />,
          color: 'text-red-500'
        };
      default:
        return {
          icon: <TrendingUpIcon className="h-5 w-5 text-gray-500" />,
          color: 'text-gray-500'
        };
    }
  };
  
  // Calculate percentage for bar widths
  const getPercentage = (value, total) => {
    return total > 0 ? (value / total) * 100 : 0;
  };

  return (
    <>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Analytics</h1>
        <div>
          <select
            value={timeRange}
            onChange={handleTimeRangeChange}
            className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="7days">Last 7 Days</option>
            <option value="30days">Last 30 Days</option>
            <option value="90days">Last 90 Days</option>
            <option value="year">Last Year</option>
            <option value="all">All Time</option>
          </select>
        </div>
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
      
      {loading ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
        </div>
      ) : (
        <div className="bg-white shadow rounded-lg">
          <div className="p-6">
          {/* Summary Cards */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {/* Total Reviews */}
            <div className="bg-white shadow rounded-lg p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-500">Total Reviews</p>
                  <p className="text-2xl font-semibold text-gray-900">{stats.total_reviews}</p>
                </div>
                <div className="bg-primary-100 p-3 rounded-full">
                  <ChartBarIcon className="h-6 w-6 text-primary-600" />
                </div>
              </div>
              <div className="mt-4 flex items-center">
                {getTrendDisplay(stats.recent_trend).icon}
                <span className={`ml-2 text-sm ${getTrendDisplay(stats.recent_trend).color}`}>
                  {stats.recent_trend === 'up' ? 'Increasing' : 
                   stats.recent_trend === 'down' ? 'Decreasing' : 'Stable'}
                </span>
              </div>
            </div>
            
            {/* Average Rating */}
            <div className="bg-white shadow rounded-lg p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-500">Average Rating</p>
                  <p className="text-2xl font-semibold text-gray-900">{stats.average_rating.toFixed(1)}</p>
                </div>
                <div className="bg-yellow-100 p-3 rounded-full">
                  <StarIcon className="h-6 w-6 text-yellow-500" />
                </div>
              </div>
              <div className="mt-4 flex">
                {[1, 2, 3, 4, 5].map((star) => (
                  <StarIcon
                    key={star}
                    className={`h-5 w-5 ${
                      star <= Math.round(stats.average_rating) ? 'text-yellow-400' : 'text-gray-300'
                    }`}
                  />
                ))}
              </div>
            </div>
            
            {/* Response Rate */}
            <div className="bg-white shadow rounded-lg p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-500">Response Rate</p>
                  <p className="text-2xl font-semibold text-gray-900">{(stats.response_rate * 100).toFixed(0)}%</p>
                </div>
                <div className="bg-green-100 p-3 rounded-full">
                  <ChartPieIcon className="h-6 w-6 text-green-600" />
                </div>
              </div>
              <div className="mt-4">
                <div className="w-full bg-gray-200 rounded-full h-2.5">
                  <div 
                    className="bg-green-600 h-2.5 rounded-full" 
                    style={{ width: `${stats.response_rate * 100}%` }}
                  ></div>
                </div>
              </div>
            </div>
            
            {/* Sentiment */}
            <div className="bg-white shadow rounded-lg p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-500">Sentiment</p>
                  <p className="text-2xl font-semibold text-gray-900">
                    {stats.sentiment_distribution.positive > stats.sentiment_distribution.negative ? 'Positive' : 
                     stats.sentiment_distribution.negative > stats.sentiment_distribution.positive ? 'Negative' : 'Neutral'}
                  </p>
                </div>
                <div className="bg-blue-100 p-3 rounded-full">
                  <TrendingUpIcon className="h-6 w-6 text-blue-600" />
                </div>
              </div>
              <div className="mt-4 flex space-x-2">
                <div className="bg-green-100 h-2 flex-1 rounded-full" style={{ 
                  width: `${getPercentage(stats.sentiment_distribution.positive, 
                    stats.sentiment_distribution.positive + 
                    stats.sentiment_distribution.neutral + 
                    stats.sentiment_distribution.negative)}%` 
                }}></div>
                <div className="bg-gray-200 h-2 flex-1 rounded-full" style={{ 
                  width: `${getPercentage(stats.sentiment_distribution.neutral, 
                    stats.sentiment_distribution.positive + 
                    stats.sentiment_distribution.neutral + 
                    stats.sentiment_distribution.negative)}%` 
                }}></div>
                <div className="bg-red-100 h-2 flex-1 rounded-full" style={{ 
                  width: `${getPercentage(stats.sentiment_distribution.negative, 
                    stats.sentiment_distribution.positive + 
                    stats.sentiment_distribution.neutral + 
                    stats.sentiment_distribution.negative)}%` 
                }}></div>
              </div>
            </div>
          </div>
          
          {/* Rating Distribution */}
          <div className="bg-white shadow rounded-lg overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200">
              <h2 className="text-lg font-medium text-gray-900">Rating Distribution</h2>
            </div>
            <div className="p-6">
              <div className="space-y-4">
                {[5, 4, 3, 2, 1].map((rating) => (
                  <div key={rating} className="flex items-center">
                    <div className="w-8 text-sm font-medium text-gray-500">{rating} ★</div>
                    <div className="w-full ml-4">
                      <div className="w-full bg-gray-200 rounded-full h-2.5">
                        <div 
                          className={`h-2.5 rounded-full ${
                            rating >= 4 ? 'bg-green-500' : 
                            rating === 3 ? 'bg-yellow-500' : 'bg-red-500'
                          }`}
                          style={{ width: `${getPercentage(stats.rating_distribution[rating], stats.total_reviews)}%` }}
                        ></div>
                      </div>
                    </div>
                    <div className="w-12 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.rating_distribution[rating]}
                    </div>
                    <div className="w-16 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.total_reviews > 0 ? 
                        `${(getPercentage(stats.rating_distribution[rating], stats.total_reviews)).toFixed(1)}%` : 
                        '0%'}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
          
          {/* Platform Distribution */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-white shadow rounded-lg overflow-hidden">
              <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-lg font-medium text-gray-900">Platform Distribution</h2>
              </div>
              <div className="p-6">
                <div className="space-y-4">
                  <div className="flex items-center">
                    <div className="w-20 text-sm font-medium text-gray-500">Google</div>
                    <div className="w-full ml-4">
                      <div className="w-full bg-gray-200 rounded-full h-2.5">
                        <div 
                          className="bg-blue-500 h-2.5 rounded-full"
                          style={{ width: `${getPercentage(stats.platform_distribution.google, stats.total_reviews)}%` }}
                        ></div>
                      </div>
                    </div>
                    <div className="w-12 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.platform_distribution.google}
                    </div>
                    <div className="w-16 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.total_reviews > 0 ? 
                        `${(getPercentage(stats.platform_distribution.google, stats.total_reviews)).toFixed(1)}%` : 
                        '0%'}
                    </div>
                  </div>
                  
                  <div className="flex items-center">
                    <div className="w-20 text-sm font-medium text-gray-500">Yelp</div>
                    <div className="w-full ml-4">
                      <div className="w-full bg-gray-200 rounded-full h-2.5">
                        <div 
                          className="bg-red-500 h-2.5 rounded-full"
                          style={{ width: `${getPercentage(stats.platform_distribution.yelp, stats.total_reviews)}%` }}
                        ></div>
                      </div>
                    </div>
                    <div className="w-12 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.platform_distribution.yelp}
                    </div>
                    <div className="w-16 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.total_reviews > 0 ? 
                        `${(getPercentage(stats.platform_distribution.yelp, stats.total_reviews)).toFixed(1)}%` : 
                        '0%'}
                    </div>
                  </div>
                  
                  <div className="flex items-center">
                    <div className="w-20 text-sm font-medium text-gray-500">Facebook</div>
                    <div className="w-full ml-4">
                      <div className="w-full bg-gray-200 rounded-full h-2.5">
                        <div 
                          className="bg-indigo-500 h-2.5 rounded-full"
                          style={{ width: `${getPercentage(stats.platform_distribution.facebook, stats.total_reviews)}%` }}
                        ></div>
                      </div>
                    </div>
                    <div className="w-12 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.platform_distribution.facebook}
                    </div>
                    <div className="w-16 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.total_reviews > 0 ? 
                        `${(getPercentage(stats.platform_distribution.facebook, stats.total_reviews)).toFixed(1)}%` : 
                        '0%'}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            {/* Sentiment Distribution */}
            <div className="bg-white shadow rounded-lg overflow-hidden">
              <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-lg font-medium text-gray-900">Sentiment Distribution</h2>
              </div>
              <div className="p-6">
                <div className="space-y-4">
                  <div className="flex items-center">
                    <div className="w-20 text-sm font-medium text-gray-500">Positive</div>
                    <div className="w-full ml-4">
                      <div className="w-full bg-gray-200 rounded-full h-2.5">
                        <div 
                          className="bg-green-500 h-2.5 rounded-full"
                          style={{ width: `${getPercentage(stats.sentiment_distribution.positive, 
                            stats.sentiment_distribution.positive + 
                            stats.sentiment_distribution.neutral + 
                            stats.sentiment_distribution.negative)}%` }}
                        ></div>
                      </div>
                    </div>
                    <div className="w-12 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.sentiment_distribution.positive}
                    </div>
                    <div className="w-16 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.total_reviews > 0 ? 
                        `${(getPercentage(stats.sentiment_distribution.positive, 
                          stats.sentiment_distribution.positive + 
                          stats.sentiment_distribution.neutral + 
                          stats.sentiment_distribution.negative)).toFixed(1)}%` : 
                        '0%'}
                    </div>
                  </div>
                  
                  <div className="flex items-center">
                    <div className="w-20 text-sm font-medium text-gray-500">Neutral</div>
                    <div className="w-full ml-4">
                      <div className="w-full bg-gray-200 rounded-full h-2.5">
                        <div 
                          className="bg-gray-500 h-2.5 rounded-full"
                          style={{ width: `${getPercentage(stats.sentiment_distribution.neutral, 
                            stats.sentiment_distribution.positive + 
                            stats.sentiment_distribution.neutral + 
                            stats.sentiment_distribution.negative)}%` }}
                        ></div>
                      </div>
                    </div>
                    <div className="w-12 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.sentiment_distribution.neutral}
                    </div>
                    <div className="w-16 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.total_reviews > 0 ? 
                        `${(getPercentage(stats.sentiment_distribution.neutral, 
                          stats.sentiment_distribution.positive + 
                          stats.sentiment_distribution.neutral + 
                          stats.sentiment_distribution.negative)).toFixed(1)}%` : 
                        '0%'}
                    </div>
                  </div>
                  
                  <div className="flex items-center">
                    <div className="w-20 text-sm font-medium text-gray-500">Negative</div>
                    <div className="w-full ml-4">
                      <div className="w-full bg-gray-200 rounded-full h-2.5">
                        <div 
                          className="bg-red-500 h-2.5 rounded-full"
                          style={{ width: `${getPercentage(stats.sentiment_distribution.negative, 
                            stats.sentiment_distribution.positive + 
                            stats.sentiment_distribution.neutral + 
                            stats.sentiment_distribution.negative)}%` }}
                        ></div>
                      </div>
                    </div>
                    <div className="w-12 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.sentiment_distribution.negative}
                    </div>
                    <div className="w-16 ml-4 text-sm font-medium text-gray-500 text-right">
                      {stats.total_reviews > 0 ? 
                        `${(getPercentage(stats.sentiment_distribution.negative, 
                          stats.sentiment_distribution.positive + 
                          stats.sentiment_distribution.neutral + 
                          stats.sentiment_distribution.negative)).toFixed(1)}%` : 
                        '0%'}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>
        </div>
      )}
    </>
  );
}
