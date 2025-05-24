import { useState, useEffect, useRef } from 'react';
import { useLocation, Link } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { House, ArrowLeft, Warning, Compass, ArrowsClockwise, Lightning } from 'phosphor-react';

// Import premium components
import ParticleBackground from './ParticleBackground';
import Animated404Text from './Animated404Text';
import SmartSuggestions from './SmartSuggestions';
import InteractiveSearch from './InteractiveSearch';

// CSS for glitch effect
import './styles.css';

const PremiumNotFound = () => {
  const location = useLocation();
  const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });
  const [showEasterEgg, setShowEasterEgg] = useState(false);
  const [theme, setTheme] = useState('light');
  const timerRef = useRef(null);
  
  // Track mouse position for 3D effects
  useEffect(() => {
    const handleMouseMove = (e) => {
      setMousePosition({ x: e.clientX, y: e.clientY });
    };
    
    window.addEventListener('mousemove', handleMouseMove);
    
    return () => {
      window.removeEventListener('mousemove', handleMouseMove);
    };
  }, []);
  
  // Set timer for easter egg
  useEffect(() => {
    timerRef.current = setTimeout(() => {
      setShowEasterEgg(true);
    }, 10000); // Show easter egg after 10 seconds
    
    return () => {
      clearTimeout(timerRef.current);
    };
  }, []);
  
  // Toggle theme
  const toggleTheme = () => {
    setTheme(theme === 'light' ? 'dark' : 'light');
  };
  
  return (
    <div className={`min-h-screen flex flex-col relative overflow-hidden ${theme === 'dark' ? 'bg-gray-900 text-white' : 'bg-white text-gray-900'}`}>
      {/* Particle background */}
      <ParticleBackground mousePosition={mousePosition} />
      
      {/* Theme toggle */}
      <button 
        onClick={toggleTheme}
        className="absolute top-4 right-4 z-50 p-2 rounded-full bg-gray-100 dark:bg-gray-800 shadow-md transition-all duration-300 hover:shadow-lg"
      >
        <motion.div
          animate={{ rotate: theme === 'dark' ? 180 : 0 }}
          transition={{ duration: 0.5 }}
        >
          {theme === 'dark' ? (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clipRule="evenodd" />
            </svg>
          ) : (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
              <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
            </svg>
          )}
        </motion.div>
      </button>
      
      {/* Header */}
      <header className="relative z-10 pt-6 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <nav className="relative flex items-center justify-between sm:h-10">
            <div className="flex items-center flex-grow flex-shrink-0 lg:flex-grow-0">
              <div className="flex items-center justify-between w-full md:w-auto">
                <Link to="/" className="flex items-center">
                  <img
                    className="h-8 w-auto sm:h-10"
                    src="/logo.svg"
                    alt="AI Auto Review"
                  />
                  <span className="ml-2 text-xl font-bold text-primary-600">AI Auto Review</span>
                </Link>
              </div>
            </div>
            <div className="hidden md:block md:ml-10 md:pr-4 md:space-x-8">
              <Link to="/dashboard" className="font-medium text-primary-600 hover:text-primary-500 transition-colors duration-300">
                Dashboard
              </Link>
              <Link to="/support" className="font-medium text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors duration-300">
                Support
              </Link>
            </div>
          </nav>
        </div>
      </header>
      
      {/* Main content */}
      <main className="flex-grow flex items-center relative z-10 px-4 sm:px-6 lg:px-8 py-16">
        <div className="max-w-7xl mx-auto w-full">
          <div className="text-center">
            {/* 3D animated 404 text */}
            <Animated404Text mousePosition={mousePosition} />
            
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.5 }}
              className="mt-4"
            >
              <h2 className="text-3xl font-extrabold tracking-tight sm:text-4xl">
                Page not found
              </h2>
              <p className="mt-3 max-w-2xl mx-auto text-xl text-gray-500 dark:text-gray-400">
                The page you're looking for doesn't exist or has been moved.
              </p>
            </motion.div>
            
            {/* Interactive search */}
            <InteractiveSearch />
            
            {/* Smart suggestions */}
            <div className="mt-12 max-w-3xl mx-auto">
              <SmartSuggestions currentPath={location.pathname} />
            </div>
            
            {/* Quick navigation */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.8 }}
              className="mt-12"
            >
              <Link
                to="/"
                className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 mr-4"
              >
                <House weight="bold" className="mr-2 -ml-1 h-5 w-5" />
                Back to Home
              </Link>
              <button
                onClick={() => window.history.back()}
                className="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300"
              >
                <ArrowLeft weight="bold" className="mr-2 -ml-1 h-5 w-5" />
                Go Back
              </button>
            </motion.div>
          </div>
        </div>
      </main>
      
      {/* Footer */}
      <footer className="relative z-10 py-6 px-4 sm:px-6 lg:px-8 border-t border-gray-200 dark:border-gray-800">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center">
          <div className="flex items-center">
            <Warning weight="duotone" className="h-5 w-5 text-amber-500 mr-2" />
            <p className="text-sm text-gray-500 dark:text-gray-400">
              Error Code: 404 | Path: {location.pathname}
            </p>
          </div>
          
          <div className="mt-4 md:mt-0 flex items-center">
            <button
              onClick={() => window.location.reload()}
              className="inline-flex items-center text-sm text-primary-600 hover:text-primary-500 mr-4"
            >
              <ArrowsClockwise weight="bold" className="mr-1 h-4 w-4" />
              Refresh
            </button>
            
            <Link
              to="/support"
              className="inline-flex items-center text-sm text-primary-600 hover:text-primary-500"
            >
              <Lightning weight="bold" className="mr-1 h-4 w-4" />
              Report Issue
            </Link>
          </div>
        </div>
      </footer>
      
      {/* Easter egg mini-game */}
      <AnimatePresence>
        {showEasterEgg && (
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.9 }}
            className="fixed bottom-4 right-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 z-50 max-w-xs"
          >
            <div className="flex items-start">
              <div className="flex-shrink-0">
                <Compass weight="duotone" className="h-6 w-6 text-primary-600" />
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-gray-900 dark:text-white">Lost Explorer Mini-Game</h3>
                <div className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                  <p>Found something unexpected? Play a quick game while you're here!</p>
                </div>
                <div className="mt-3">
                  <button
                    onClick={() => window.open('/games/maze-explorer', '_blank')}
                    className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                  >
                    Play Now
                  </button>
                  <button
                    onClick={() => setShowEasterEgg(false)}
                    className="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                  >
                    Dismiss
                  </button>
                </div>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default PremiumNotFound;
