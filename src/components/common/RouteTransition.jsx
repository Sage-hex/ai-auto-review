import { useState, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';

export default function RouteTransition({ children }) {
  const location = useLocation();
  const [isLoading, setIsLoading] = useState(false);
  const [prevPathname, setPrevPathname] = useState('');

  useEffect(() => {
    // Only show preloader when changing routes, not on initial load
    if (prevPathname && prevPathname !== location.pathname) {
      setIsLoading(true);
      
      // Simulate loading time (in a real app, this would be based on actual data loading)
      const timer = setTimeout(() => {
        setIsLoading(false);
      }, 800);
      
      return () => clearTimeout(timer);
    }
    
    setPrevPathname(location.pathname);
  }, [location.pathname, prevPathname]);

  return (
    <>
      <AnimatePresence mode="wait">
        {isLoading && (
          <motion.div
            key="loader"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.2 }}
            className="fixed inset-0 flex items-center justify-center bg-white bg-opacity-80 z-50"
          >
            <div className="flex flex-col items-center">
              <div className="relative">
                <div className="h-16 w-16 rounded-full border-t-4 border-b-4 border-primary-600 animate-spin"></div>
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="h-10 w-10 rounded-full bg-white"></div>
                </div>
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="h-6 w-6 rounded-full border-t-4 border-b-4 border-indigo-500 animate-spin"></div>
                </div>
              </div>
              <p className="mt-4 text-gray-700 font-medium">Loading...</p>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
      
      <motion.div
        key={location.pathname}
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        transition={{ duration: 0.3 }}
      >
        {children}
      </motion.div>
    </>
  );
}
