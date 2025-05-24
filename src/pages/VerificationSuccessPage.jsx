import { useEffect, useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { motion } from 'framer-motion';
import { CheckCircle, ArrowRight, ShieldCheck, Check } from 'phosphor-react';
import { useAuth } from '../contexts/AuthContext';
import '../styles/premium-animations.css';

export default function VerificationSuccessPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { currentUser } = useAuth();
  const [countdown, setCountdown] = useState(5);
  
  useEffect(() => {
    // Set page title
    document.title = 'Verification Successful | AI Auto Review';
    
    // Redirect to dashboard after countdown
    const timer = setTimeout(() => {
      navigate('/dashboard');
    }, 5000);
    
    // Countdown timer
    const interval = setInterval(() => {
      setCountdown(prev => {
        if (prev <= 1) {
          clearInterval(interval);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
    
    return () => {
      clearTimeout(timer);
      clearInterval(interval);
    };
  }, [navigate]);
  
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
          
          {/* Success illustration */}
          <div className="flex flex-col items-center justify-center">
            <div className="w-32 h-32 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center mb-4 animate-float">
              <CheckCircle weight="duotone" className="w-20 h-20 text-white" />
            </div>
            <h2 className="text-2xl font-bold mb-2 premium-gradient-text">Account Verified!</h2>
            <p className="text-center text-white/80 max-w-xs">
              Your account has been successfully verified. You now have full access to all features.
            </p>
          </div>
          
          {/* Features list */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold">What's next?</h3>
            <ul className="space-y-2">
              <li className="flex items-start">
                <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">Monitor reviews across all platforms</span>
              </li>
              <li className="flex items-start">
                <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">Generate AI-powered responses in seconds</span>
              </li>
              <li className="flex items-start">
                <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">Track analytics and improve your reputation</span>
              </li>
            </ul>
          </div>
          
          {/* Footer */}
          <div className="text-white/60 text-xs">
            © 2025 AI Auto Review
          </div>
        </div>
      </div>
      
      {/* Right side - Success content */}
      <div className="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-6 lg:p-12 bg-white/90 backdrop-blur-lg shadow-xl relative z-10 lg:rounded-l-3xl overflow-hidden">
        {/* Premium glass effect decorative elements */}
        <div className="absolute -top-24 -right-24 w-48 h-48 bg-green-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div className="absolute top-0 -right-4 w-32 h-32 bg-primary-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div className="absolute -bottom-16 -left-16 w-40 h-40 bg-accent-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
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
          
          <motion.div variants={itemVariants} className="text-center mb-6 sm:mb-8">
            <div className="flex justify-center mb-4 sm:mb-6">
              <div className="h-16 w-16 sm:h-24 sm:w-24 rounded-full bg-green-50 flex items-center justify-center animate-pulse-premium">
                <CheckCircle weight="fill" className="h-10 w-10 sm:h-14 sm:w-14 text-green-500" />
              </div>
            </div>
            
            <h2 className="text-xl sm:text-3xl font-bold premium-gradient-text inline-block mb-2">Verification Successful!</h2>
            <p className="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">
              Your account has been verified
            </p>
            
            <p className="text-xs sm:text-sm text-gray-500 mb-6">
              You will be redirected to your dashboard in <span className="font-bold text-primary-600">{countdown}</span> seconds
            </p>
          </motion.div>
          
          <motion.div variants={itemVariants}>
            <button
              onClick={() => navigate('/dashboard')}
              className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-lg text-xs sm:text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 premium-btn-hover animate-pulse-premium"
            >
              Go to Dashboard <ArrowRight weight="bold" className="ml-2 h-4 w-4 sm:h-5 sm:w-5" />
            </button>
          </motion.div>
          
          <motion.div variants={itemVariants} className="mt-6 sm:mt-8 text-center">
            <div className="space-y-3 sm:space-y-4">
              <h3 className="text-sm sm:text-base font-medium text-gray-900">What you can do now:</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                <div className="p-3 bg-gray-50 rounded-lg border border-gray-100 text-left premium-card-hover">
                  <h4 className="text-xs sm:text-sm font-medium text-gray-900 mb-1">Monitor Reviews</h4>
                  <p className="text-xs text-gray-500">Track reviews across all platforms in one place</p>
                </div>
                <div className="p-3 bg-gray-50 rounded-lg border border-gray-100 text-left premium-card-hover">
                  <h4 className="text-xs sm:text-sm font-medium text-gray-900 mb-1">AI Responses</h4>
                  <p className="text-xs text-gray-500">Generate professional responses in seconds</p>
                </div>
                <div className="p-3 bg-gray-50 rounded-lg border border-gray-100 text-left premium-card-hover">
                  <h4 className="text-xs sm:text-sm font-medium text-gray-900 mb-1">Analytics</h4>
                  <p className="text-xs text-gray-500">Track sentiment and identify trends</p>
                </div>
                <div className="p-3 bg-gray-50 rounded-lg border border-gray-100 text-left premium-card-hover">
                  <h4 className="text-xs sm:text-sm font-medium text-gray-900 mb-1">Team Collaboration</h4>
                  <p className="text-xs text-gray-500">Invite team members and assign tasks</p>
                </div>
              </div>
            </div>
          </motion.div>
          
          <motion.div variants={itemVariants} className="mt-6 text-center">
            <p className="text-xs sm:text-sm text-gray-500">
              Need help? <a href="#" className="font-medium text-primary-600 hover:text-primary-500 transition-colors">Contact Support</a>
            </p>
          </motion.div>
        </motion.div>
      </div>
    </div>
  );
}
