import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { House, ArrowLeft, Warning } from 'phosphor-react';

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

export default function PremiumNotFound() {
  return (
    <div className="flex min-h-screen">
      {/* Left Panel - Branding */}
      <div className="hidden md:flex md:w-1/2 bg-gradient-to-br from-primary-600 to-primary-800 text-white p-10 flex-col justify-between">
        <div>
          <motion.div 
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="mb-8"
          >
            <img src="/logo-white.svg" alt="AI Auto Review" className="h-12" />
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.3, duration: 0.8 }}
          >
            <h1 className="text-4xl font-bold mb-6">Oops! Page not found</h1>
            <p className="text-xl opacity-80 mb-8 max-w-md">
              We couldn't find the page you're looking for. Let's get you back on track.
            </p>
            
            <div className="space-y-4">
              <div className="flex items-start">
                <div className="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-white bg-opacity-20 mt-1">
                  <span className="text-sm font-bold">1</span>
                </div>
                <p className="ml-4 text-lg">Check the URL for typos</p>
              </div>
              
              <div className="flex items-start">
                <div className="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-white bg-opacity-20 mt-1">
                  <span className="text-sm font-bold">2</span>
                </div>
                <p className="ml-4 text-lg">Return to the dashboard</p>
              </div>
              
              <div className="flex items-start">
                <div className="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-white bg-opacity-20 mt-1">
                  <span className="text-sm font-bold">3</span>
                </div>
                <p className="ml-4 text-lg">Contact support if the issue persists</p>
              </div>
            </div>
          </motion.div>
        </div>
        
        <div className="text-sm opacity-70">
          © {new Date().getFullYear()} AI Auto Review. All rights reserved.
        </div>
      </div>
      
      {/* Right Panel - Content */}
      <div className="w-full md:w-1/2 flex items-center justify-center p-8">
        <motion.div 
          className="max-w-md w-full"
          variants={containerVariants}
          initial="hidden"
          animate="visible"
        >
          <motion.div 
            variants={itemVariants}
            className="text-center mb-8"
          >
            <div className="flex justify-center mb-6">
              <div className="h-32 w-32 rounded-full bg-red-50 flex items-center justify-center">
                <Warning weight="duotone" className="h-16 w-16 text-red-500" />
              </div>
            </div>
            
            <h2 className="text-3xl font-bold text-gray-900 mb-2">404</h2>
            <p className="text-xl text-gray-600 mb-8">Page not found</p>
            
            <p className="text-gray-500 mb-8">
              The page you're looking for doesn't exist or has been moved. Let's get you back on track.
            </p>
          </motion.div>
          
          <motion.div variants={itemVariants} className="space-y-4">
            <Link
              to="/dashboard"
              className="flex items-center justify-center w-full px-4 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <House weight="bold" className="h-5 w-5 mr-2" />
              Go to Dashboard
            </Link>
            
            <Link
              to="/"
              className="flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <ArrowLeft weight="bold" className="h-5 w-5 mr-2" />
              Back to Home
            </Link>
          </motion.div>
          
          <motion.div 
            variants={itemVariants}
            className="mt-8 text-center"
          >
            <p className="text-sm text-gray-500">
              Need help? <a href="#" className="font-medium text-primary-600 hover:text-primary-500">Contact Support</a>
            </p>
          </motion.div>
        </motion.div>
      </div>
    </div>
  );
}
