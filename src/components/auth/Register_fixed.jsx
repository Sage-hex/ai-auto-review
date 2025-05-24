import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import '../../styles/premium-animations.css';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Envelope, 
  Lock, 
  User,
  Buildings,
  Phone,
  SpinnerGap, 
  Eye, 
  EyeSlash, 
  GoogleLogo, 
  FacebookLogo, 
  AppleLogo, 
  ShieldCheck, 
  Warning,
  Check
} from 'phosphor-react';

export default function Register() {
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    company: '',
    phone: '',
    password: '',
    confirmPassword: ''
  });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [step, setStep] = useState(1);
  
  const { register } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const validateStep1 = () => {
    if (!formData.firstName || !formData.lastName || !formData.email) {
      setError('Please fill in all required fields');
      return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(formData.email)) {
      setError('Please enter a valid email address');
      return false;
    }
    
    return true;
  };

  const validateStep2 = () => {
    if (!formData.password || !formData.confirmPassword) {
      setError('Please fill in all required fields');
      return false;
    }
    
    if (formData.password.length < 8) {
      setError('Password must be at least 8 characters long');
      return false;
    }
    
    if (formData.password !== formData.confirmPassword) {
      setError('Passwords do not match');
      return false;
    }
    
    return true;
  };

  const handleNextStep = () => {
    if (validateStep1()) {
      setError('');
      setStep(2);
    }
  };

  const handlePrevStep = () => {
    setError('');
    setStep(1);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateStep2()) {
      return;
    }
    
    try {
      setError('');
      setIsLoading(true);
      
      await register(formData);
      navigate('/verify-otp');
    } catch (err) {
      setError(err.response?.data?.message || 'Registration failed. Please try again.');
    } finally {
      setIsLoading(false);
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
      <form onSubmit={handleSubmit} className="h-full w-full flex flex-col lg:flex-row">
        {/* Left side - Branding */}
        <div className="hidden lg:flex lg:w-1/2 relative overflow-hidden">
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
            
            {/* Features list */}
            <div className="space-y-4">
              <h2 className="text-xl font-bold">Why choose us?</h2>
              <ul className="space-y-2">
                <li className="flex items-start">
                  <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                  <span className="ml-2 text-sm">AI-powered responses to reviews in seconds</span>
                </li>
                <li className="flex items-start">
                  <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                  <span className="ml-2 text-sm">Centralized dashboard for all review platforms</span>
                </li>
                <li className="flex items-start">
                  <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                  <span className="ml-2 text-sm">Sentiment analysis and trend reporting</span>
                </li>
                <li className="flex items-start">
                  <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                  <span className="ml-2 text-sm">Customizable templates and brand voice</span>
                </li>
              </ul>
            </div>
            
            {/* Testimonial - Minimal size */}
            <div className="bg-white/10 backdrop-blur-sm rounded-lg p-3 max-w-xs">
              <div className="flex items-center space-x-1 mb-2">
                {[...Array(5)].map((_, i) => (
                  <svg key={i} className="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                ))}
              </div>
              <p className="text-white/90 italic text-sm">
                "AI Auto Review has transformed how we handle customer feedback. The AI responses save us countless hours."
              </p>
              <div className="mt-2 flex items-center">
                <div className="h-8 w-8 rounded-full bg-primary-400"></div>
                <div className="ml-2">
                  <p className="font-medium text-sm">Sarah Johnson</p>
                  <p className="text-xs text-white/70">CMO, Stellar Hospitality</p>
                </div>
              </div>
            </div>
            
            {/* Footer */}
            <div className="text-white/60 text-xs">
              © 2025 AI Auto Review
            </div>
          </div>
        </div>
        
        {/* Right side - Registration form */}
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
            
            <motion.div variants={itemVariants} className="mb-4">
              <h2 className="text-lg sm:text-xl font-bold premium-gradient-text inline-block">Create your account</h2>
              <p className="mt-1 text-xs sm:text-sm text-gray-600">
                Get started with your 14-day free trial
              </p>
            </motion.div>
            
            <AnimatePresence>
              {error && (
                <motion.div 
                  className="mb-3 flex items-center p-3 rounded-lg bg-red-50 border border-red-100"
                  initial={{ opacity: 0, y: -10 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0 }}
                >
                  <Warning weight="fill" className="flex-shrink-0 h-4 w-4 text-red-500" />
                  <p className="ml-2 text-xs text-red-700">{error}</p>
                </motion.div>
              )}
            </AnimatePresence>
            
            {/* Step indicator */}
            <motion.div variants={itemVariants} className="mb-4">
              <div className="flex items-center">
                <div className={`flex-1 h-1 ${step === 1 ? 'bg-primary-500' : 'bg-gray-200'}`}></div>
                <div className={`flex-1 h-1 ${step === 2 ? 'bg-primary-500' : 'bg-gray-200'}`}></div>
              </div>
              <div className="flex justify-between mt-1">
                <span className="text-xs font-medium text-gray-500">Account Info</span>
                <span className="text-xs font-medium text-gray-500">Security</span>
              </div>
            </motion.div>
            
            {/* Step 1: Account Information */}
            {step === 1 && (
              <motion.div 
                variants={itemVariants}
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 20 }}
                className="space-y-2 sm:space-y-3"
              >
                <div className="grid grid-cols-2 gap-2 sm:gap-3">
                  <div>
                    <label htmlFor="firstName" className="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                      First name*
                    </label>
                    <div className="relative">
                      <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <User weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                      </div>
                      <input
                        id="firstName"
                        name="firstName"
                        type="text"
                        required
                        value={formData.firstName}
                        onChange={handleChange}
                        className="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-xs sm:text-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                        placeholder="John"
                      />
                    </div>
                  </div>
                  <div>
                    <label htmlFor="lastName" className="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                      Last name*
                    </label>
                    <input
                      id="lastName"
                      name="lastName"
                      type="text"
                      required
                      value={formData.lastName}
                      onChange={handleChange}
                      className="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-xs sm:text-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                      placeholder="Doe"
                    />
                  </div>
                </div>
                
                <div>
                  <label htmlFor="email" className="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                    Email address*
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
                      value={formData.email}
                      onChange={handleChange}
                      className="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-xs sm:text-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                      placeholder="you@example.com"
                    />
                  </div>
                </div>
                
                <div>
                  <label htmlFor="company" className="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                    Company
                  </label>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <Buildings weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                    </div>
                    <input
                      id="company"
                      name="company"
                      type="text"
                      value={formData.company}
                      onChange={handleChange}
                      className="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-xs sm:text-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                      placeholder="Company name"
                    />
                  </div>
                </div>
                
                <div>
                  <label htmlFor="phone" className="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                    Phone number
                  </label>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <Phone weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                    </div>
                    <input
                      id="phone"
                      name="phone"
                      type="tel"
                      value={formData.phone}
                      onChange={handleChange}
                      className="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-xs sm:text-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                      placeholder="+1 (555) 123-4567"
                    />
                  </div>
                </div>
                
                <div className="pt-2">
                  <button
                    type="button"
                    onClick={handleNextStep}
                    className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-lg text-xs sm:text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 premium-btn-hover animate-pulse-premium"
                  >
                    Continue
                  </button>
                </div>
              </motion.div>
            )}
            
            {/* Step 2: Password */}
            {step === 2 && (
              <motion.div 
                variants={itemVariants}
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -20 }}
                className="space-y-2 sm:space-y-3"
              >
                <div>
                  <label htmlFor="password" className="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                    Password*
                  </label>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <Lock weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                    </div>
                    <input
                      id="password"
                      name="password"
                      type={showPassword ? "text" : "password"}
                      autoComplete="new-password"
                      required
                      value={formData.password}
                      onChange={handleChange}
                      className="appearance-none block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-xs sm:text-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                      placeholder="••••••••"
                    />
                    <button
                      type="button"
                      className="absolute inset-y-0 right-0 pr-3 flex items-center"
                      onClick={() => setShowPassword(!showPassword)}
                    >
                      {showPassword ? (
                        <EyeSlash weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 hover:text-gray-600" />
                      ) : (
                        <Eye weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 hover:text-gray-600" />
                      )}
                    </button>
                  </div>
                  <p className="mt-1 text-xs text-gray-500">
                    Password must be at least 8 characters
                  </p>
                </div>
                
                <div>
                  <label htmlFor="confirmPassword" className="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                    Confirm password*
                  </label>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <Lock weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                    </div>
                    <input
                      id="confirmPassword"
                      name="confirmPassword"
                      type={showConfirmPassword ? "text" : "password"}
                      autoComplete="new-password"
                      required
                      value={formData.confirmPassword}
                      onChange={handleChange}
                      className="appearance-none block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-xs sm:text-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                      placeholder="••••••••"
                    />
                    <button
                      type="button"
                      className="absolute inset-y-0 right-0 pr-3 flex items-center"
                      onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                    >
                      {showConfirmPassword ? (
                        <EyeSlash weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 hover:text-gray-600" />
                      ) : (
                        <Eye weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 hover:text-gray-600" />
                      )}
                    </button>
                  </div>
                </div>
                
                <div className="pt-2 flex space-x-2 sm:space-x-3">
                  <button
                    type="button"
                    onClick={handlePrevStep}
                    className="flex-1 flex justify-center items-center px-3 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
                  >
                    Back
                  </button>
                  <button
                    type="submit"
                    disabled={isLoading}
                    className="flex-1 flex justify-center items-center px-3 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed premium-btn-hover animate-pulse-premium"
                  >
                    {isLoading ? (
                      <>
                        <SpinnerGap weight="bold" className="animate-spin -ml-1 mr-2 h-4 w-4 sm:h-5 sm:w-5" />
                        Creating account...
                      </>
                    ) : (
                      'Create account'
                    )}
                  </button>
                </div>
              </motion.div>
            )}
            
            <motion.div variants={itemVariants} className="mt-4">
              <div className="relative">
                <div className="absolute inset-0 flex items-center">
                  <div className="w-full border-t border-gray-300"></div>
                </div>
                <div className="relative flex justify-center text-xs">
                  <span className="px-2 bg-white text-gray-500">Or continue with</span>
                </div>
              </div>
              
              <div className="mt-3 grid grid-cols-3 gap-2">
                <button
                  type="button"
                  className="w-full inline-flex justify-center py-2 px-2 sm:py-2.5 sm:px-4 border border-gray-200 rounded-lg shadow-sm bg-white text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all duration-200"
                >
                  <GoogleLogo weight="fill" className="h-4 w-4 sm:h-5 sm:w-5 text-[#4285F4]" />
                </button>
                <button
                  type="button"
                  className="w-full inline-flex justify-center py-2 px-2 sm:py-2.5 sm:px-4 border border-gray-200 rounded-lg shadow-sm bg-white text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all duration-200"
                >
                  <FacebookLogo weight="fill" className="h-4 w-4 sm:h-5 sm:w-5 text-[#1877F2]" />
                </button>
                <button
                  type="button"
                  className="w-full inline-flex justify-center py-2 px-2 sm:py-2.5 sm:px-4 border border-gray-200 rounded-lg shadow-sm bg-white text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all duration-200"
                >
                  <AppleLogo weight="fill" className="h-4 w-4 sm:h-5 sm:w-5 text-black" />
                </button>
              </div>
            </motion.div>
            
            <motion.p variants={itemVariants} className="mt-3 sm:mt-4 text-center text-xs sm:text-sm text-gray-500">
              Already have an account?{' '}
              <Link to="/login" className="font-medium text-primary-600 hover:text-primary-500 transition-colors">
                Sign in
              </Link>
            </motion.p>
          </motion.div>
        </div>
      </form>
    </div>
  );
}
