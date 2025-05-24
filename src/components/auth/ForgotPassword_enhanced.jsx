import { useState } from 'react';
import { Link } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import axios from 'axios';
import { Envelope, ArrowLeft, ShieldCheck, Warning, CheckCircle } from 'phosphor-react';
import '../../styles/premium-animations.css';

export default function ForgotPassword() {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!email) {
      setError('Please enter your email address');
      return;
    }
    
    try {
      setLoading(true);
      setError('');
      
      // Make API request to reset password
      await axios.post('/backend/api/endpoints/auth/forgot-password.php', { email });
      
      setSuccess(true);
    } catch (err) {
      console.error('Error requesting password reset:', err);
      setError(err.response?.data?.message || 'Failed to send password reset email. Please try again.');
    } finally {
      setLoading(false);
    }
  };

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
    <div className="h-screen flex flex-col lg:flex-row overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
      {/* Left side - Branding */}
      <div className="w-full lg:w-1/2 bg-gradient-to-br from-primary-600 to-primary-800 relative overflow-hidden hidden lg:block">
        {/* Background gradient with overlay */}
        <div className="absolute inset-0 bg-gradient-to-br from-primary-600 to-primary-900"></div>
        <div className="absolute inset-0 bg-[url('/assets/pattern-bg.png')] opacity-10"></div>
        
        {/* Decorative elements */}
        <div className="absolute top-1/4 left-1/4 w-64 h-64 bg-primary-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
        <div className="absolute bottom-1/4 right-1/4 w-80 h-80 bg-accent-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
        
        <div className="relative flex flex-col justify-between h-full p-8 text-white z-10">
          {/* Logo and branding */}
          <div>
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                <ShieldCheck weight="fill" className="w-5 h-5 text-primary-600" />
              </div>
              <h1 className="text-xl font-bold">AI Auto Review</h1>
            </div>
            <p className="mt-3 text-sm font-light max-w-md">
              Manage and respond to customer reviews across all platforms.
            </p>
          </div>
          
          {/* Password reset illustration */}
          <div className="flex flex-col items-center justify-center">
            <div className="w-32 h-32 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center mb-4 animate-float">
              <Envelope weight="duotone" className="w-16 h-16 text-white" />
            </div>
            <h2 className="text-2xl font-bold mb-2">Password Reset</h2>
            <p className="text-center text-white/80 max-w-xs">
              Don't worry, it happens to the best of us. We'll help you get back into your account.
            </p>
          </div>
          
          {/* Features list */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold">Remember AI Auto Review?</h3>
            <ul className="space-y-2">
              <li className="flex items-start">
                <CheckCircle weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">AI-powered responses to reviews in seconds</span>
              </li>
              <li className="flex items-start">
                <CheckCircle weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">Centralized dashboard for all platforms</span>
              </li>
              <li className="flex items-start">
                <CheckCircle weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">Sentiment analysis and trend reporting</span>
              </li>
            </ul>
          </div>
          
          {/* Footer */}
          <div className="text-white/60 text-xs">
            © 2025 AI Auto Review
          </div>
        </div>
      </div>
      
      {/* Right side - Password Reset Form */}
      <div className="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-6 lg:p-12 bg-white/90 backdrop-blur-lg shadow-xl relative z-10 lg:rounded-l-3xl overflow-hidden">
        {/* Premium glass effect decorative elements */}
        <div className="absolute -top-24 -right-24 w-48 h-48 bg-primary-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div className="absolute top-0 -right-4 w-32 h-32 bg-accent-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div className="absolute -bottom-16 -left-16 w-40 h-40 bg-secondary-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        
        <motion.div 
          className="w-full max-w-md"
          initial="hidden"
          animate="visible"
          variants={containerVariants}
        >
          {/* Mobile logo - only shown on smaller screens */}
          <motion.div variants={itemVariants} className="flex justify-center mb-4 sm:mb-6 lg:hidden">
            <div className="flex items-center space-x-3">
              <div className="w-8 h-8 sm:w-10 sm:h-10 bg-primary-600 rounded-xl flex items-center justify-center">
                <ShieldCheck weight="fill" className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
              </div>
              <h1 className="text-xl sm:text-2xl font-bold text-gray-900">AI Auto Review</h1>
            </div>
          </motion.div>
          
          <motion.div variants={itemVariants} className="text-center mb-6">
            <h2 className="text-xl sm:text-2xl font-bold premium-gradient-text inline-block mb-2">Reset your password</h2>
            <p className="text-gray-600 text-xs sm:text-sm">
              Enter your email address and we'll send you a link to reset your password.
            </p>
          </motion.div>
          
          <AnimatePresence>
            {error && (
              <motion.div 
                className="mb-4 flex items-center p-3 rounded-lg bg-red-50 border border-red-100"
                initial={{ opacity: 0, y: -10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0 }}
              >
                <Warning weight="fill" className="flex-shrink-0 h-4 w-4 text-red-500" />
                <p className="ml-2 text-xs text-red-700">{error}</p>
              </motion.div>
            )}
          </AnimatePresence>
          
          {success ? (
            <motion.div
              variants={itemVariants}
              className="rounded-md bg-green-50 p-4 border border-green-100"
            >
              <div className="flex">
                <div className="flex-shrink-0">
                  <CheckCircle weight="fill" className="h-5 w-5 text-green-500" />
                </div>
                <div className="ml-3">
                  <h3 className="text-sm font-medium text-green-800">Password reset email sent</h3>
                  <div className="mt-2 text-xs sm:text-sm text-green-700">
                    <p>
                      We've sent a password reset link to <span className="font-medium">{email}</span>. Please check your inbox and follow the instructions.
                    </p>
                  </div>
                  <div className="mt-4">
                    <Link
                      to="/login"
                      className="inline-flex items-center text-xs sm:text-sm font-medium text-primary-600 hover:text-primary-500 transition-colors"
                    >
                      <ArrowLeft weight="bold" className="mr-1 h-3 w-3 sm:h-4 sm:w-4" />
                      Return to login
                    </Link>
                  </div>
                </div>
              </div>
            </motion.div>
          ) : (
            <form onSubmit={handleSubmit}>
              <motion.div variants={itemVariants} className="space-y-4">
                <div>
                  <label htmlFor="email" className="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                    Email address
                  </label>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <Envelope weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                    </div>
                    <input
                      id="email"
                      name="email"
                      type="email"
                      autoComplete="email"
                      required
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      className="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-xs sm:text-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                      placeholder="you@example.com"
                    />
                  </div>
                </div>
                
                <div className="pt-2">
                  <button
                    type="submit"
                    disabled={loading}
                    className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-lg text-xs sm:text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed premium-btn-hover animate-pulse-premium"
                  >
                    {loading ? (
                      <svg className="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                    ) : (
                      'Send reset link'
                    )}
                  </button>
                </div>
              </motion.div>
              
              <motion.div variants={itemVariants} className="mt-6 text-center">
                <Link 
                  to="/login" 
                  className="inline-flex items-center text-xs sm:text-sm font-medium text-primary-600 hover:text-primary-500 transition-colors"
                >
                  <ArrowLeft weight="bold" className="mr-1 h-3 w-3 sm:h-4 sm:w-4" />
                  Back to login
                </Link>
              </motion.div>
            </form>
          )}
        </motion.div>
      </div>
    </div>
  );
}
