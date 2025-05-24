import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { motion, AnimatePresence } from 'framer-motion';
import '../../styles/premium-animations.css';
import { 
  Envelope, 
  Lock, 
  SpinnerGap, 
  Eye, 
  EyeSlash, 
  GoogleLogo, 
  FacebookLogo, 
  AppleLogo, 
  ShieldCheck, 
  Warning
} from 'phosphor-react';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  
  const { login } = useAuth();
  const navigate = useNavigate();

  // Check for saved email
  useEffect(() => {
    const savedEmail = localStorage.getItem('rememberedEmail');
    if (savedEmail) {
      setEmail(savedEmail);
      setRememberMe(true);
    }
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!email || !password) {
      setError('Please fill in all fields');
      return;
    }
    
    try {
      setError('');
      setIsLoading(true);
      
      await login(email, password);
      
      // Save email if remember me is checked
      if (rememberMe) {
        localStorage.setItem('rememberedEmail', email);
      } else {
        localStorage.removeItem('rememberedEmail');
      }
      
      navigate('/dashboard');
    } catch (err) {
      setError(err.response?.data?.message || 'Invalid credentials. Please try again.');
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
        
        {/* Right side - Login form */}
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
            
            <motion.h1 variants={itemVariants} className="text-xl sm:text-2xl font-bold mb-1 text-gray-900">
              Welcome back
            </motion.h1>
            <motion.p variants={itemVariants} className="text-gray-600 mb-3 sm:mb-4 text-xs sm:text-sm">
              Sign in to your account to continue
            </motion.p>
            
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
            
            <motion.div variants={itemVariants} className="space-y-3 sm:space-y-4">
              <div>
                <h2 className="text-lg sm:text-xl font-bold premium-gradient-text inline-block mb-3">Sign in to your account</h2>
                <div className="space-y-2 sm:space-y-3">
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
                  
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <label htmlFor="password" className="block text-xs sm:text-sm font-medium text-gray-700">
                        Password
                      </label>
                      <Link to="/forgot-password" className="text-xs sm:text-sm font-medium text-primary-600 hover:text-primary-500 transition-colors">
                        Forgot?
                      </Link>
                    </div>
                    <div className="relative">
                      <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Lock weight="bold" className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                      </div>
                      <input
                        id="password"
                        name="password"
                        type={showPassword ? "text" : "password"}
                        autoComplete="current-password"
                        required
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
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
                  </div>
                  
                  <div className="flex justify-between items-center text-xs">
                    <div className="flex items-center">
                      <input
                        id="remember-me"
                        name="remember-me"
                        type="checkbox"
                        checked={rememberMe}
                        onChange={(e) => setRememberMe(e.target.checked)}
                        className="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                      />
                      <label htmlFor="remember-me" className="ml-1 block text-gray-900">
                        Remember me
                      </label>
                    </div>
                  </div>
                </div>
                
                <div className="mt-4 sm:mt-6">
                  <button
                    type="submit"
                    disabled={isLoading}
                    className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-lg text-xs sm:text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed premium-btn-hover animate-pulse-premium"
                  >
                    {isLoading ? (
                      <>
                        <SpinnerGap weight="bold" className="animate-spin -ml-1 mr-2 h-4 w-4 sm:h-5 sm:w-5" />
                        Signing in...
                      </>
                    ) : (
                      'Sign in'
                    )}
                  </button>
                </div>
              </div>
              
              <motion.div variants={itemVariants} className="mt-3">
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
                Don't have an account?{' '}
                <Link to="/register" className="font-medium text-primary-600 hover:text-primary-500 transition-colors">
                  Start free trial
                </Link>
              </motion.p>
            </motion.div>
          </motion.div>
        </div>
      </form>
    </div>
  );
}
