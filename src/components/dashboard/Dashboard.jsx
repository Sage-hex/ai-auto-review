import { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { useReviews } from '../../contexts/ReviewContext';
import { Link } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';

// Phosphor Icons
import { 
  Star, 
  ArrowsClockwise, 
  CheckCircle,
  Buildings,
  TrendUp,
  Bell,
  GoogleLogo,
  FacebookLogo,
  Sparkle,
  ClockClockwise,
  CaretDown,
  IdentificationBadge,
  User,
  CircleNotch,
  X
} from 'phosphor-react';

// Animation variants for staggered animations
const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1
    }
  }
};

const itemVariants = {
  hidden: { y: 20, opacity: 0 },
  visible: {
    y: 0,
    opacity: 1,
    transition: {
      type: 'spring',
      stiffness: 300,
      damping: 24
    }
  }
};

export default function Dashboard() {
  const { business } = useAuth();
  const { 
    reviewStats, 
    pendingResponses, 
    fetchReviewStats, 
    fetchPendingResponses, 
    syncReviews, 
    loading 
  } = useReviews();
  
  // State for UI interactions
  const [activeTab, setActiveTab] = useState('overview');
  const [isNotificationOpen, setIsNotificationOpen] = useState(false);
  const [isSyncing, setIsSyncing] = useState(false);
  const [syncSuccess, setSyncSuccess] = useState(false);
  const [syncError, setSyncError] = useState(false);
  const [timeRange, setTimeRange] = useState('7d');
  
  // Mock data for dashboard elements
  const mockData = {
    responseRate: 92,
    avgResponseTime: '1.2 hours',
    platforms: [
      { name: 'Google', reviews: 128, rating: 4.7, icon: 'google' },
      { name: 'Facebook', reviews: 84, rating: 4.5, icon: 'facebook' },
      { name: 'Yelp', reviews: 42, rating: 4.2, icon: 'yelp' },
      { name: 'TripAdvisor', reviews: 36, rating: 4.6, icon: 'tripadvisor' }
    ],
    recentActivity: [
      { type: 'new_review', platform: 'Google', time: '2 hours ago', rating: 4 },
      { type: 'response_approved', platform: 'Yelp', time: '5 hours ago', rating: 3 },
      { type: 'new_review', platform: 'Facebook', time: '1 day ago', rating: 5 }
    ],
    sentimentTrend: { positive: 12, neutral: 5, negative: 2, change: '+15%' }
  };

  useEffect(() => {
    fetchReviewStats();
    fetchPendingResponses();
  }, []);

  const handleSyncReviews = async () => {
    try {
      setIsSyncing(true);
      await syncReviews();
      setSyncSuccess(true);
      setTimeout(() => setSyncSuccess(false), 3000);
    } catch (error) {
      console.error('Error syncing reviews:', error);
      setSyncError(true);
      setTimeout(() => setSyncError(false), 3000);
    } finally {
      setIsSyncing(false);
    }
  };

  // Function to render stars based on rating
  const renderStars = (rating) => {
    return Array(5)
      .fill()
      .map((_, i) => (
        <Star
          key={i}
          weight={i < rating ? "fill" : "regular"}
          className={`h-5 w-5 ${
            i < rating ? 'text-yellow-500' : 'text-gray-300'
          }`}
        />
      ));
  };

  return (
    <>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <div className="flex space-x-2">
          <button
            onClick={handleSyncReviews}
            disabled={isSyncing}
            className="flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
          >
            <ArrowsClockwise className="h-5 w-5 mr-2" />
            {isSyncing ? 'Syncing...' : 'Sync Reviews'}
          </button>
        </div>
      </div>
      
      <div className="bg-white shadow rounded-lg mb-8">
        <div className="p-6">
        {/* Dashboard Content */}
        <motion.div 
          className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4"
          initial="hidden"
          animate="visible"
          variants={containerVariants}
        >
          <motion.div variants={itemVariants} className="flex-1">
            <h2 className="text-2xl font-semibold mb-2 text-primary-600">
              Welcome back, {business?.name || 'User'}
            </h2>
            <p className="text-gray-600">Monitor your review performance and manage AI-powered responses</p>
          </motion.div>
          
          <motion.div variants={itemVariants} className="flex items-center space-x-3">
            {/* Time Range Selector */}
            <div className="relative">
              <button 
                className="flex items-center text-sm px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 transition-colors duration-200"
                onClick={() => setTimeRange(timeRange === '7d' ? '30d' : '7d')}
              >
                <ClockClockwise weight="bold" className="h-4 w-4 mr-2 text-primary-500" />
                {timeRange === '7d' ? 'Last 7 days' : 'Last 30 days'}
                <CaretDown weight="bold" className="h-3 w-3 ml-2 text-gray-500" />
              </button>
            </div>
            
            {/* Sync Button */}
            <button 
              onClick={handleSyncReviews}
              disabled={isSyncing}
              className="flex items-center text-sm px-4 py-2 bg-primary-600 text-white rounded-md shadow-sm hover:bg-primary-700 transition-colors duration-200 disabled:opacity-50"
            >
              {isSyncing ? (
                <CircleNotch weight="bold" className="h-4 w-4 mr-2 animate-spin" />
              ) : (
                <ArrowsClockwise weight="bold" className="h-4 w-4 mr-2" />
              )}
              Sync Reviews
            </button>
            
            {/* Notification Bell */}
            <div className="relative">
              <button 
                className="p-2 rounded-full bg-white border border-gray-300 shadow-sm hover:bg-gray-50 transition-colors duration-200 relative"
                onClick={() => setIsNotificationOpen(!isNotificationOpen)}
              >
                <Bell weight="bold" className="h-5 w-5 text-gray-700" />
                <span className="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
              </button>
              
              {/* Notification Dropdown */}
              <AnimatePresence>
                {isNotificationOpen && (
                  <motion.div 
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 10 }}
                    className="absolute right-0 mt-2 w-80 shadow-lg rounded-xl bg-white border border-gray-200 z-10"
                  >
                    <div className="p-4 border-b border-gray-200 flex justify-between items-center">
                      <h3 className="font-medium text-gray-900">Notifications</h3>
                      <button onClick={() => setIsNotificationOpen(false)} className="text-gray-400 hover:text-gray-500">
                        <X weight="bold" className="h-4 w-4" />
                      </button>
                    </div>
                    <div className="max-h-96 overflow-y-auto">
                      {mockData.recentActivity.map((activity, idx) => (
                        <div key={idx} className="p-4 border-b border-gray-200 last:border-0 hover:bg-gray-50 transition-colors duration-150">
                          <div className="flex items-start">
                            <div className="flex-shrink-0 mr-3">
                              {activity.type === 'new_review' ? (
                                <div className="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                  <Star weight="fill" className="h-4 w-4 text-blue-600" />
                                </div>
                              ) : (
                                <div className="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                  <CheckCircle weight="fill" className="h-4 w-4 text-green-600" />
                                </div>
                              )}
                            </div>
                            <div>
                              <p className="text-sm font-medium text-gray-900">
                                {activity.type === 'new_review' ? 'New review on ' : 'Response approved on '}
                                <span className="font-semibold">{activity.platform}</span>
                              </p>
                              <div className="flex mt-1">
                                {renderStars(activity.rating)}
                              </div>
                              <p className="text-xs text-gray-500 mt-1">{activity.time}</p>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          </motion.div>
        </motion.div>

        {/* Pending Responses Section */}
        <motion.div 
          className="mb-8"
          variants={containerVariants}
          initial="hidden"
          animate="visible"
        >
          <motion.div variants={itemVariants} className="flex items-center justify-between mb-4">
            <h2 className="text-2xl font-bold text-gray-900">
              Pending Responses
            </h2>
            <Link to="/reviews" className="flex items-center text-primary-600 hover:text-primary-700 font-medium text-sm transition-colors duration-200">
              View All
              <CaretDown weight="bold" className="h-4 w-4 ml-1 transform rotate-270" />
            </Link>
          </motion.div>
          
          {loading ? (
            <motion.div 
              variants={itemVariants}
              className="flex justify-center py-12"
            >
              <CircleNotch weight="bold" className="h-8 w-8 animate-spin text-primary-600" />
            </motion.div>
          ) : pendingResponses && pendingResponses.length > 0 ? (
            <motion.div 
              variants={containerVariants}
              className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
            >
              {pendingResponses.map((review, index) => (
                <motion.div 
                  key={index} 
                  variants={itemVariants}
                  className="bg-white rounded-xl shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-300"
                >
                  <div className="flex items-start justify-between mb-3">
                    <div className="flex items-center">
                      <div className="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                        <User weight="bold" className="h-5 w-5 text-gray-500" />
                      </div>
                      <div>
                        <h3 className="font-medium text-gray-900">
                          {review.reviewer_name || 'Anonymous'}
                        </h3>
                        <div className="flex items-center">
                          {renderStars(review.rating)}
                          <span className="text-xs text-gray-500 ml-2">
                            {new Date(review.date).toLocaleDateString()}
                          </span>
                        </div>
                      </div>
                    </div>
                    <div className="bg-gray-100 p-1 rounded-full">
                      {review.source === 'google' && <GoogleLogo weight="fill" className="h-5 w-5 text-[#4285F4]" />}
                      {review.source === 'facebook' && <FacebookLogo weight="fill" className="h-5 w-5 text-[#1877F2]" />}
                      {review.source === 'yelp' && <IdentificationBadge weight="fill" className="h-5 w-5 text-[#FF1A1A]" />}
                    </div>
                  </div>
                  
                  <div className="mb-4">
                    <p className="text-gray-700 text-sm line-clamp-3">
                      {review.content}
                    </p>
                  </div>
                  
                  <div className="border-t border-gray-200 pt-4">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center bg-blue-50 px-2 py-1 rounded-full">
                        <Sparkle weight="fill" className="h-4 w-4 text-blue-500 mr-1" />
                        <span className="text-xs font-medium text-blue-700">
                          AI Response Ready
                        </span>
                      </div>
                      <Link 
                        to={`/reviews/${review.id}`} 
                        className="flex items-center text-sm px-3 py-1.5 bg-primary-600 text-white rounded-md shadow-sm hover:bg-primary-700 transition-colors duration-200"
                      >
                        Review & Approve
                      </Link>
                    </div>
                  </div>
                </motion.div>
              ))}
            </motion.div>
          ) : (
            <motion.div 
              variants={itemVariants}
              className="bg-white rounded-xl shadow-md p-8 border border-gray-200 text-center"
            >
              <div className="flex justify-center mb-4">
                <div className="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center">
                  <CheckCircle weight="fill" className="h-8 w-8 text-green-600" />
                </div>
              </div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">
                All caught up!
              </h3>
              <p className="text-gray-600 mb-6">
                You have no pending responses to approve. Check back later or sync your reviews to check for new ones.
              </p>
              <button 
                onClick={handleSyncReviews}
                disabled={isSyncing}
                className="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md shadow-sm hover:bg-primary-700 transition-colors duration-200 disabled:opacity-50"
              >
                {isSyncing ? (
                  <>
                    <CircleNotch weight="bold" className="animate-spin h-4 w-4 mr-2" />
                    Syncing...
                  </>
                ) : (
                  <>
                    <ArrowsClockwise weight="bold" className="h-4 w-4 mr-2" />
                    Sync Reviews
                  </>
                )}
              </button>
            </motion.div>
          )}
        </motion.div>
        
        {/* Main Dashboard Content */}
        <motion.div 
          className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8"
          variants={containerVariants}
          initial="hidden"
          animate="visible"
        >
          {/* Stats Cards */}
          <motion.div 
            variants={itemVariants}
            className="bg-white rounded-xl shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-300"
          >
            <div className="flex items-center justify-between mb-4">
              <h3 className="font-medium text-gray-900">Overall Rating</h3>
              <div className="flex items-center bg-yellow-50 px-3 py-1 rounded-full">
                <Star weight="fill" className="h-5 w-5 text-yellow-500 mr-1" />
                <span className="font-bold text-lg text-gray-900">{reviewStats?.averageRating || '4.6'}</span>
              </div>
            </div>
            <div className="flex items-center justify-between text-sm">
              <span className="text-gray-600">Total Reviews</span>
              <span className="font-medium text-gray-900">{reviewStats?.totalReviews || '290'}</span>
            </div>
            <div className="mt-4 pt-4 border-t border-gray-200">
              <div className="flex items-center justify-between text-sm">
                <span className="text-gray-600">Response Rate</span>
                <span className="font-medium text-gray-900">{mockData.responseRate}%</span>
              </div>
              <div className="flex items-center justify-between text-sm mt-2">
                <span className="text-gray-600">Avg Response Time</span>
                <span className="font-medium text-gray-900">{mockData.avgResponseTime}</span>
              </div>
            </div>
          </motion.div>
          
          {/* Sentiment Trend */}
          <motion.div 
            variants={itemVariants}
            className="bg-white rounded-xl shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-300"
          >
            <div className="flex items-center justify-between mb-4">
              <h3 className="font-medium text-gray-900">Sentiment Trend</h3>
              <div className="flex items-center text-green-600 text-sm font-medium bg-green-50 px-3 py-1 rounded-full">
                <TrendUp weight="bold" className="h-4 w-4 mr-1" />
                {mockData.sentimentTrend.change}
              </div>
            </div>
            <div className="grid grid-cols-3 gap-2 mb-4">
              <div className="bg-green-50 p-3 rounded-lg">
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-600">Positive</span>
                  <span className="text-xs font-medium text-green-600">{mockData.sentimentTrend.positive}</span>
                </div>
                <div className="mt-2 flex items-center">
                  <div className="h-2 bg-green-500 rounded-full" style={{ width: `${(mockData.sentimentTrend.positive / (mockData.sentimentTrend.positive + mockData.sentimentTrend.neutral + mockData.sentimentTrend.negative)) * 100}%` }}></div>
                </div>
              </div>
              <div className="bg-blue-50 p-3 rounded-lg">
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-600">Neutral</span>
                  <span className="text-xs font-medium text-blue-600">{mockData.sentimentTrend.neutral}</span>
                </div>
                <div className="mt-2 flex items-center">
                  <div className="h-2 bg-blue-500 rounded-full" style={{ width: `${(mockData.sentimentTrend.neutral / (mockData.sentimentTrend.positive + mockData.sentimentTrend.neutral + mockData.sentimentTrend.negative)) * 100}%` }}></div>
                </div>
              </div>
              <div className="bg-red-50 p-3 rounded-lg">
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-600">Negative</span>
                  <span className="text-xs font-medium text-red-600">{mockData.sentimentTrend.negative}</span>
                </div>
                <div className="mt-2 flex items-center">
                  <div className="h-2 bg-red-500 rounded-full" style={{ width: `${(mockData.sentimentTrend.negative / (mockData.sentimentTrend.positive + mockData.sentimentTrend.neutral + mockData.sentimentTrend.negative)) * 100}%` }}></div>
                </div>
              </div>
            </div>
            <div className="text-xs text-gray-500">
              Sentiment analysis based on customer reviews in the selected time period.
            </div>
          </motion.div>
          
          {/* Platform Distribution */}
          <motion.div 
            variants={itemVariants}
            className="bg-white rounded-xl shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-300"
          >
            <h3 className="font-medium text-gray-900 mb-4">Platform Distribution</h3>
            <div className="space-y-4">
              {mockData.platforms.map((platform, idx) => (
                <div key={idx} className="flex items-center">
                  <div className="w-8 h-8 rounded-full flex items-center justify-center mr-3 bg-gray-100">
                    {platform.icon === 'google' && <GoogleLogo weight="fill" className="h-5 w-5 text-[#4285F4]" />}
                    {platform.icon === 'facebook' && <FacebookLogo weight="fill" className="h-5 w-5 text-[#1877F2]" />}
                    {platform.icon === 'yelp' && <IdentificationBadge weight="fill" className="h-5 w-5 text-[#FF1A1A]" />}
                    {platform.icon === 'tripadvisor' && <Buildings weight="fill" className="h-5 w-5 text-[#00AA6C]" />}
                  </div>
                  <div className="flex-1">
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-900">{platform.name}</span>
                      <div className="flex items-center">
                        <Star weight="fill" className="h-4 w-4 text-yellow-500 mr-1" />
                        <span className="text-sm font-medium text-gray-900">{platform.rating}</span>
                      </div>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className="bg-primary-500 h-2 rounded-full" 
                        style={{ width: `${(platform.reviews / Math.max(...mockData.platforms.map(p => p.reviews))) * 100}%` }}
                      ></div>
                    </div>
                    <div className="mt-1 text-xs text-gray-500">
                      {platform.reviews} reviews
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </motion.div>
        </motion.div>
        </div>
      </div>
    </>
  );
}
