import { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { MagnifyingGlass, ArrowRight } from 'phosphor-react';
import { useNavigate } from 'react-router-dom';

const InteractiveSearch = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const [suggestions, setSuggestions] = useState([]);
  const inputRef = useRef(null);
  const navigate = useNavigate();
  
  // Predefined routes for suggestions
  const routes = [
    { path: '/dashboard', label: 'Dashboard' },
    { path: '/dashboard/reviews', label: 'Reviews' },
    { path: '/dashboard/analytics', label: 'Analytics' },
    { path: '/dashboard/responses', label: 'Responses' },
    { path: '/dashboard/users', label: 'User Management' },
    { path: '/dashboard/platforms', label: 'Platforms' },
    { path: '/dashboard/settings', label: 'Settings' },
    { path: '/subscription', label: 'Subscription Plans' }
  ];
  
  // Filter suggestions based on search term
  useEffect(() => {
    if (searchTerm.length > 0) {
      const filtered = routes.filter(route => 
        route.label.toLowerCase().includes(searchTerm.toLowerCase())
      );
      setSuggestions(filtered.slice(0, 4)); // Limit to 4 suggestions
    } else {
      setSuggestions([]);
    }
  }, [searchTerm]);
  
  // Auto-focus the input on mount
  useEffect(() => {
    setTimeout(() => {
      if (inputRef.current) {
        inputRef.current.focus();
      }
    }, 1000);
  }, []);
  
  // Handle form submission
  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (searchTerm.trim() && suggestions.length > 0) {
      navigate(suggestions[0].path);
    }
  };
  
  // Handle suggestion click
  const handleSuggestionClick = (path) => {
    navigate(path);
  };
  
  return (
    <div className="mt-8 max-w-md mx-auto">
      <form onSubmit={handleSubmit}>
        <div className="relative">
          <motion.div
            initial={{ scale: 0.95, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ 
              type: 'spring',
              stiffness: 300,
              damping: 20,
              delay: 0.2
            }}
            className="relative"
          >
            <MagnifyingGlass 
              weight="bold" 
              className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-5 w-5"
            />
            
            <input
              ref={inputRef}
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              onFocus={() => setIsTyping(true)}
              onBlur={() => setTimeout(() => setIsTyping(false), 200)}
              placeholder="Search for a page..."
              className="w-full pl-10 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all duration-300 shadow-sm"
            />
            
            <button
              type="submit"
              className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-primary-600 text-white p-1.5 rounded-lg hover:bg-primary-700 transition-colors duration-300"
              disabled={!searchTerm.trim() || suggestions.length === 0}
            >
              <ArrowRight weight="bold" className="h-4 w-4" />
            </button>
          </motion.div>
          
          {/* Typing animation */}
          {isTyping && (
            <motion.div
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="absolute -top-6 left-0 text-xs text-gray-500"
            >
              <span className="inline-flex items-center">
                <span className="mr-1">Searching</span>
                <span className="typing-animation">
                  <span>.</span><span>.</span><span>.</span>
                </span>
              </span>
            </motion.div>
          )}
        </div>
      </form>
      
      {/* Search suggestions */}
      <AnimatePresence>
        {isTyping && suggestions.length > 0 && (
          <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: 10 }}
            className="mt-2 bg-white rounded-lg shadow-lg border border-gray-100 overflow-hidden z-10 relative"
          >
            <ul>
              {suggestions.map((suggestion, index) => (
                <motion.li
                  key={suggestion.path}
                  initial={{ opacity: 0, x: -10 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: index * 0.05 }}
                  onClick={() => handleSuggestionClick(suggestion.path)}
                  className="px-4 py-2 hover:bg-gray-50 cursor-pointer flex items-center"
                >
                  <MagnifyingGlass weight="light" className="mr-2 text-gray-400 h-4 w-4" />
                  <span>{suggestion.label}</span>
                  {index === 0 && (
                    <span className="ml-auto text-xs text-primary-600 font-medium">Press Enter</span>
                  )}
                </motion.li>
              ))}
            </ul>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default InteractiveSearch;
