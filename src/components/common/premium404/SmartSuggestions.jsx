import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { MagnifyingGlass, Lightbulb, Lightning } from 'phosphor-react';
import { Link } from 'react-router-dom';

const SmartSuggestions = ({ currentPath }) => {
  const [suggestions, setSuggestions] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  
  // Generate smart suggestions based on the current path
  useEffect(() => {
    setIsLoading(true);
    
    // Simulate API call to get suggestions
    setTimeout(() => {
      // Parse the current path to generate relevant suggestions
      const pathSegments = currentPath.split('/').filter(Boolean);
      const generatedSuggestions = [];
      
      // Default suggestions
      const defaultSuggestions = [
        { path: '/dashboard', label: 'Dashboard', icon: 'dashboard' },
        { path: '/dashboard/reviews', label: 'Reviews', icon: 'reviews' },
        { path: '/dashboard/analytics', label: 'Analytics', icon: 'analytics' }
      ];
      
      // Add path-specific suggestions
      if (pathSegments.length > 0) {
        // Check if path contains 'review' or similar
        if (currentPath.includes('review')) {
          generatedSuggestions.push(
            { path: '/dashboard/reviews', label: 'All Reviews', icon: 'reviews', relevance: 'high' },
            { path: '/dashboard/responses', label: 'Response Templates', icon: 'templates', relevance: 'medium' }
          );
        }
        
        // Check if path contains 'analytic' or similar
        if (currentPath.includes('analytic') || currentPath.includes('stat') || currentPath.includes('report')) {
          generatedSuggestions.push(
            { path: '/dashboard/analytics', label: 'Analytics Dashboard', icon: 'analytics', relevance: 'high' },
            { path: '/dashboard/reports', label: 'Reports', icon: 'reports', relevance: 'medium' }
          );
        }
        
        // Check if path contains 'user' or similar
        if (currentPath.includes('user') || currentPath.includes('account') || currentPath.includes('profile')) {
          generatedSuggestions.push(
            { path: '/dashboard/users', label: 'User Management', icon: 'users', relevance: 'high' },
            { path: '/dashboard/settings', label: 'Account Settings', icon: 'settings', relevance: 'medium' }
          );
        }
      }
      
      // If no specific suggestions, use defaults
      const finalSuggestions = generatedSuggestions.length > 0 
        ? generatedSuggestions 
        : defaultSuggestions;
      
      setSuggestions(finalSuggestions);
      setIsLoading(false);
    }, 800); // Simulate loading delay
  }, [currentPath]);
  
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
  
  return (
    <div className="mt-8">
      <div className="flex items-center mb-4">
        <Lightbulb weight="duotone" className="w-5 h-5 text-primary-500 mr-2" />
        <h3 className="text-lg font-semibold text-gray-800">Smart Suggestions</h3>
      </div>
      
      <AnimatePresence>
        {isLoading ? (
          <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="flex justify-center py-6"
          >
            <div className="flex items-center space-x-2">
              <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-primary-600"></div>
              <span className="text-sm text-gray-500">Analyzing your request...</span>
            </div>
          </motion.div>
        ) : (
          <motion.div
            variants={containerVariants}
            initial="hidden"
            animate="visible"
            className="grid grid-cols-1 md:grid-cols-2 gap-3"
          >
            {suggestions.map((suggestion, index) => (
              <motion.div key={index} variants={itemVariants}>
                <Link
                  to={suggestion.path}
                  className="flex items-center p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-all duration-300"
                >
                  <div className="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center mr-3">
                    {suggestion.relevance === 'high' && (
                      <Lightning weight="fill" className="h-5 w-5 text-primary-600" />
                    )}
                    {!suggestion.relevance && (
                      <MagnifyingGlass weight="duotone" className="h-5 w-5 text-primary-600" />
                    )}
                  </div>
                  <div>
                    <p className="font-medium text-gray-800">{suggestion.label}</p>
                    <p className="text-xs text-gray-500">
                      {suggestion.relevance === 'high' 
                        ? 'Best match for your request' 
                        : suggestion.relevance === 'medium'
                          ? 'Related to your request'
                          : 'You might be looking for this'}
                    </p>
                  </div>
                </Link>
              </motion.div>
            ))}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default SmartSuggestions;
