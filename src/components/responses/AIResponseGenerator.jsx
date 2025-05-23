import { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { Sparkle, Lightning, Robot, ArrowsClockwise } from 'phosphor-react';
import { motion } from 'framer-motion';

export default function AIResponseGenerator({ review, onGenerated, onCancel }) {
  const { business } = useAuth();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [businessType, setBusinessType] = useState('');
  const [tone, setTone] = useState('professional');
  const [includePromotion, setIncludePromotion] = useState(false);
  const [promotionText, setPromotionText] = useState('');
  
  // Animation variants
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
  
  const handleGenerate = async () => {
    try {
      setLoading(true);
      setError('');
      
      // Prepare data for the AI response generation
      const data = {
        review_id: review.id,
        business_name: business?.name || '',
        business_type: businessType,
        tone: tone,
        include_promotion: includePromotion,
        promotion_text: includePromotion ? promotionText : ''
      };
      
      // Make API request to generate response
      const response = await fetch(`/backend/api/endpoints/ai/generate-response.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify(data)
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to generate AI response');
      }
      
      const responseData = await response.json();
      
      // Call the onGenerated callback with the generated response
      onGenerated(responseData.data.response);
    } catch (err) {
      console.error('Error generating AI response:', err);
      setError(err.message || 'Failed to generate AI response');
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <motion.div 
      className="bg-white shadow-lg rounded-lg p-6 border border-gray-200"
      initial="hidden"
      animate="visible"
      variants={containerVariants}
    >
      <div className="flex items-center justify-between mb-6">
        <motion.div className="flex items-center" variants={itemVariants}>
          <Sparkle weight="fill" className="w-6 h-6 text-primary-500 mr-2" />
          <h2 className="text-xl font-bold text-gray-900">AI Response Generator</h2>
        </motion.div>
        <motion.div variants={itemVariants}>
          <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
            <Robot className="w-4 h-4 mr-1" />
            Powered by OpenAI GPT
          </span>
        </motion.div>
      </div>
      
      {error && (
        <motion.div 
          className="bg-red-50 border-l-4 border-red-400 p-4 mb-6"
          variants={itemVariants}
        >
          <div className="flex">
            <div className="ml-3">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          </div>
        </motion.div>
      )}
      
      <motion.div className="mb-6" variants={itemVariants}>
        <div className="bg-gray-50 p-4 rounded-lg mb-4">
          <div className="flex items-center mb-2">
            <div className="font-medium text-gray-700">Review from {review.user_name}</div>
            <div className="ml-2 text-sm text-gray-500">({review.rating} stars)</div>
          </div>
          <p className="text-gray-700">{review.content}</p>
        </div>
      </motion.div>
      
      <motion.div className="space-y-4" variants={containerVariants}>
        <motion.div variants={itemVariants}>
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
        </motion.div>
        
        <motion.div variants={itemVariants}>
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
        </motion.div>
        
        <motion.div variants={itemVariants}>
          <div className="flex items-center mb-2">
            <input
              type="checkbox"
              id="includePromotion"
              checked={includePromotion}
              onChange={(e) => setIncludePromotion(e.target.checked)}
              className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            />
            <label htmlFor="includePromotion" className="ml-2 block text-sm font-medium text-gray-700">
              Include Promotion
            </label>
          </div>
          
          {includePromotion && (
            <textarea
              id="promotionText"
              value={promotionText}
              onChange={(e) => setPromotionText(e.target.value)}
              rows={2}
              className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              placeholder="e.g., Mention our 10% discount for returning customers"
            ></textarea>
          )}
        </motion.div>
        
        <motion.div className="flex justify-end space-x-3 pt-4" variants={itemVariants}>
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
                <ArrowsClockwise className="animate-spin -ml-1 mr-2 h-4 w-4" />
                Generating...
              </>
            ) : (
              <>
                <Lightning className="-ml-1 mr-2 h-4 w-4" />
                Generate AI Response
              </>
            )}
          </button>
        </motion.div>
      </motion.div>
    </motion.div>
  );
}
