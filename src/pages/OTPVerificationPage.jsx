import { useState, useEffect, useRef } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import axios from 'axios';
import { useAuth } from '../contexts/AuthContext';
import { Envelope, ArrowRight, Timer, ArrowClockwise, ShieldCheck, Warning, Check } from 'phosphor-react';
import '../styles/premium-animations.css';

export default function OTPVerificationPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { loginWithToken } = useAuth();
  const [otp, setOtp] = useState(['', '', '', '', '', '']);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [countdown, setCountdown] = useState(60);
  const [canResend, setCanResend] = useState(false);
  const [userData, setUserData] = useState(null);
  const inputRefs = useRef([]);

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

  useEffect(() => {
    // Get user data from location state
    if (location.state?.userData) {
      console.log('OTP Page received userData:', location.state.userData);
      setUserData(location.state.userData);
    } else {
      console.log('No userData found in location state, checking localStorage');
      // Try to get from localStorage as fallback
      const tempUserData = localStorage.getItem('temp_registration_data');
      if (tempUserData) {
        try {
          const parsedData = JSON.parse(tempUserData);
          console.log('Found userData in localStorage:', parsedData);
          setUserData(parsedData);
        } catch (err) {
          console.error('Error parsing userData from localStorage:', err);
          navigate('/login');
        }
      } else {
        // If no user data, redirect to login
        console.log('No userData found, redirecting to login');
        navigate('/login');
      }
    }
  }, [location, navigate]);

  useEffect(() => {
    // Start countdown for resend button
    let interval = null;
    if (countdown > 0) {
      interval = setInterval(() => {
        setCountdown((prevCountdown) => prevCountdown - 1);
      }, 1000);
    } else {
      setCanResend(true);
      clearInterval(interval);
    }
    return () => clearInterval(interval);
  }, [countdown]);

  // Handle OTP input change
  const handleChange = (index, value) => {
    // Only allow numbers
    if (!/^\d*$/.test(value)) return;

    const newOtp = [...otp];
    newOtp[index] = value;
    setOtp(newOtp);

    // Auto-focus next input
    if (value && index < 5) {
      inputRefs.current[index + 1].focus();
    }
  };

  // Handle key press for backspace
  const handleKeyDown = (index, e) => {
    if (e.key === 'Backspace' && !otp[index] && index > 0) {
      // Focus previous input on backspace if current input is empty
      inputRefs.current[index - 1].focus();
    }
  };

  // Handle paste event
  const handlePaste = (e) => {
    e.preventDefault();
    const pastedData = e.clipboardData.getData('text');
    
    // Check if pasted data is a 6-digit number
    if (/^\d{6}$/.test(pastedData)) {
      const digits = pastedData.split('');
      setOtp(digits);
      
      // Focus the last input
      inputRefs.current[5].focus();
    }
  };

  // Handle OTP verification
  const handleVerify = async () => {
    const otpValue = otp.join('');
    
    // Check if OTP is complete
    if (otpValue.length !== 6) {
      setError('Please enter all 6 digits of the OTP');
      return;
    }

    setLoading(true);
    setError('');

    try {
      console.log('Verifying OTP with userData:', userData);
      
      // Make sure we have the user ID
      const userId = userData?.user?.id || userData?.data?.user?.id;
      
      if (!userId) {
        throw new Error('User ID not found. Please try registering again.');
      }
      
      const response = await axios.post('/backend/api/endpoints/auth/otp.php?route=verify', {
        user_id: userId,
        otp: otpValue
      });

      console.log('OTP verification response:', response.data);

      if (response.data.success) {
        setSuccess(true);
        
        // Clear temporary registration data
        localStorage.removeItem('temp_registration_data');
        
        // Login the user with the token
        if (response.data.data && response.data.data.token) {
          loginWithToken(response.data.data.token, response.data.data.user);
          
          // Redirect to verification success page after a short delay
          setTimeout(() => {
            navigate('/verification-success');
          }, 1500);
        } else {
          console.error('Token or user data missing in verification response');
          setError('Verification succeeded but login failed. Please try logging in manually.');
        }
      } else {
        setError(response.data.message || 'Verification failed');
      }
    } catch (err) {
      console.error('Verification error:', err);
      setError(err.response?.data?.message || err.message || 'An error occurred during verification');
    } finally {
      setLoading(false);
    }
  };

  // Handle resend OTP
  const handleResend = async () => {
    if (!canResend || loading) return;
    
    setLoading(true);
    setError('');
    setCanResend(false);
    setCountdown(60);

    try {
      console.log('Resending OTP with userData:', userData);
      
      // Make sure we have the user ID and email
      const userId = userData?.user?.id || userData?.data?.user?.id;
      const userEmail = userData?.user?.email || userData?.data?.user?.email || userData?.verification_email;
      
      if (!userId || !userEmail) {
        throw new Error('User information not found. Please try registering again.');
      }
      
      const response = await axios.post('/backend/api/endpoints/auth/otp.php?route=resend', {
        user_id: userId,
        email: userEmail
      });

      console.log('Resend OTP response:', response.data);

      if (response.data.success) {
        // Show success message
        setSuccess(true);
        setTimeout(() => setSuccess(false), 3000);
      } else {
        setError(response.data.message || 'Failed to resend OTP');
      }
    } catch (err) {
      console.error('Resend error:', err);
      setError(err.response?.data?.message || err.message || 'An error occurred while resending OTP');
    } finally {
      setLoading(false);
    }
  };

  if (!userData) {
    return <div className="flex justify-center items-center h-screen">Loading...</div>;
  }

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
          
          {/* Verification illustration */}
          <div className="flex flex-col items-center justify-center">
            <div className="w-32 h-32 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center mb-4">
              <Envelope weight="duotone" className="w-16 h-16 text-white" />
            </div>
            <h2 className="text-2xl font-bold mb-2 text-white">Email Verification</h2>
            <p className="text-center text-white/80 max-w-xs mb-4">
              We've sent a verification code to your email address. Please check your inbox.
            </p>
            
            {/* Display OTP for development purposes */}
            {userData?.dev_otp && (
              <div className="mb-4 p-3 bg-yellow-50 border border-yellow-100 rounded-md">
                <p className="text-xs text-yellow-800 font-medium">Development Mode</p>
                <p className="text-sm font-mono font-bold text-yellow-700">
                  Your OTP code: {userData.dev_otp}
                </p>
                <p className="text-xs text-yellow-600 mt-1">This display will be removed in production.</p>
              </div>
            )}
          </div>
          
          {/* Features list */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold">What's next?</h3>
            <ul className="space-y-2">
              <li className="flex items-start">
                <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">Verify your email to activate your account</span>
              </li>
              <li className="flex items-start">
                <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">Access your personalized dashboard</span>
              </li>
              <li className="flex items-start">
                <Check weight="bold" className="w-5 h-5 text-accent-400 flex-shrink-0 mt-0.5" />
                <span className="ml-2 text-sm">Start managing your customer reviews</span>
              </li>
            </ul>
          </div>
          
          {/* Footer */}
          <div className="text-white/60 text-xs">
            &copy; 2025 AI Auto Review
          </div>
        </div>
      </div>
      
      {/* Right side - OTP Form */}
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
          <AnimatePresence>
            {error && (
              <motion.div 
                className="mb-4 p-3 bg-red-50 text-red-700 rounded-md text-sm"
                initial={{ opacity: 0, y: -10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0 }}
              >
                {error}
              </motion.div>
            )}
          </AnimatePresence>
          
          <AnimatePresence>
            {success && (
              <motion.div 
                className="mb-4 flex items-center p-3 rounded-lg bg-green-50 border border-green-100"
                initial={{ opacity: 0, y: -10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0 }}
              >
                <Check weight="fill" className="flex-shrink-0 h-4 w-4 text-green-500" />
                <p className="ml-2 text-xs text-green-700">
                  {otp.join('').length === 6 ? 'Verification successful! Redirecting...' : 'Code resent successfully. Please check your inbox.'}
                </p>
              </motion.div>
            )}
          </AnimatePresence>
          
          <motion.div variants={itemVariants} className="mb-6">
            <div className="flex justify-between space-x-2 sm:space-x-3">
              {otp.map((digit, index) => (
                <input
                  key={index}
                  ref={(el) => (inputRefs.current[index] = el)}
                  type="text"
                  maxLength={1}
                  value={digit}
                  onChange={(e) => handleChange(index, e.target.value)}
                  onKeyDown={(e) => handleKeyDown(index, e)}
                  onPaste={index === 0 ? handlePaste : undefined}
                  className="w-10 h-12 sm:w-12 sm:h-14 text-center text-lg sm:text-xl font-bold border border-gray-300 rounded-md shadow-sm premium-input-focus backdrop-blur-sm bg-white/80 transition-all duration-300"
                  disabled={loading}
                />
              ))}
            </div>
          </motion.div>
          
          <motion.div variants={itemVariants}>
            <button
              onClick={handleVerify}
              disabled={loading || otp.join('').length !== 6}
              className={`flex items-center justify-center w-full px-4 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white ${
                loading || otp.join('').length !== 6
                  ? 'bg-gray-400 cursor-not-allowed'
                  : 'bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed premium-btn-hover animate-pulse-premium'
              }`}
            >
              {loading ? (
                <svg className="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              ) : (
                <>
                  Verify Account <ArrowRight weight="bold" className="ml-2 h-5 w-5" />
                </>
              )}
            </button>
          </motion.div>
          
          <motion.div variants={itemVariants} className="mt-6 text-center">
            <div className="flex items-center justify-center text-sm text-gray-500 mb-2">
              <Timer weight="duotone" className="mr-1 h-4 w-4" />
              {canResend ? (
                <span>You can resend the code now</span>
              ) : (
                <span>Resend code in {countdown} seconds</span>
              )}
            </div>
            <button
              onClick={handleResend}
              disabled={!canResend || loading}
              className={`text-xs sm:text-sm font-medium ${!canResend || loading ? 'text-gray-400 cursor-not-allowed' : 'text-primary-600 hover:text-primary-500 transition-colors'}`}
            >
              <span className="flex items-center justify-center">
                <ArrowClockwise weight="bold" className="mr-1 h-3 w-3 sm:h-4 sm:w-4" />
                Resend verification code
              </span>
            </button>
          </motion.div>
        </motion.div>
      </div>
    </div>
  );
}
