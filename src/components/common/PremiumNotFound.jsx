import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { House, ArrowLeft } from 'phosphor-react';

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
            transition={{ duration: 0.5, delay: 0.2 }}
          >
            <h1 className="text-3xl font-bold mb-6">Page Not Found</h1>
            <p className="text-lg opacity-90 max-w-md">
              It seems you've ventured into uncharted territory. Don't worry, we're here to help you find your way back.
            </p>
          </motion.div>
        </div>
        
        <motion.div 
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.5, delay: 0.4 }}
          className="rounded-xl bg-white/10 p-6 backdrop-blur-sm"
        >
          <p className="text-lg font-medium mb-2">AI Auto Review</p>
          <p className="opacity-80">
            "The most comprehensive solution for managing and automating your business's online reviews."
          </p>
          <div className="mt-4 flex items-center">
            <img 
              src="https://randomuser.me/api/portraits/men/32.jpg" 
              alt="Testimonial" 
              className="h-10 w-10 rounded-full mr-3" 
            />
            <div>
              <p className="font-medium">Michael Chen</p>
              <p className="text-sm opacity-80">CEO, TechReviews Inc.</p>
            </div>
          </div>
        </motion.div>
      </div>
      
      {/* Right Panel - 404 Content */}
      <div className="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12">
        <div className="w-full max-w-md">
          <div className="text-center mb-10">
            <div className="flex justify-center mb-6 md:hidden">
              <img src="/logo.svg" alt="AI Auto Review" className="h-12" />
            </div>
            
            <motion.div
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.5 }}
              className="mb-8"
            >
              <div className="flex justify-center">
                <div className="text-9xl font-bold text-primary-600 opacity-20">404</div>
              </div>
              <h2 className="text-3xl font-bold text-gray-900 -mt-16">Page Not Found</h2>
              <p className="mt-4 text-gray-600">
                The page you're looking for doesn't exist or has been moved.
              </p>
            </motion.div>
            
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.3 }}
              className="mt-8 space-y-4"
            >
              <Link
                to="/"
                className="inline-flex items-center justify-center w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
              >
                <House size={20} className="mr-2" />
                Back to Dashboard
              </Link>
              
              <button
                onClick={() => window.history.back()}
                className="inline-flex items-center justify-center w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
              >
                <ArrowLeft size={20} className="mr-2" />
                Go Back
              </button>
            </motion.div>
          </div>
        </div>
      </div>
    </div>
  );
}
