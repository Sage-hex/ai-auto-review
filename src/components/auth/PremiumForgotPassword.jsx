import { useState } from 'react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { EnvelopeSimple, ArrowLeft, Check } from 'phosphor-react';
import axios from 'axios';

export default function PremiumForgotPassword() {
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
      
      await axios.post('/backend/api/endpoints/auth/forgot-password.php', { email });
      
      setSuccess(true);
    } catch (err) {
      console.error('Error requesting password reset:', err);
      setError(err.response?.data?.message || 'Failed to request password reset');
    } finally {
      setLoading(false);
    }
  };

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
            <h1 className="text-3xl font-bold mb-6">Password Recovery</h1>
            <p className="text-lg opacity-90 max-w-md">
              We understand that sometimes passwords can be forgotten. Let us help you regain access to your account quickly and securely.
            </p>
          </motion.div>
        </div>
        
        <motion.div 
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.5, delay: 0.4 }}
          className="rounded-xl bg-white/10 p-6 backdrop-blur-sm"
        >
          <p className="text-lg font-medium mb-2">Trusted by businesses worldwide</p>
          <p className="opacity-80">
            "AI Auto Review has transformed how we manage our online reputation. Their password recovery process is just as seamless as the rest of the platform."
          </p>
          <div className="mt-4 flex items-center">
            <img 
              src="https://randomuser.me/api/portraits/women/44.jpg" 
              alt="Testimonial" 
              className="h-10 w-10 rounded-full mr-3" 
            />
            <div>
              <p className="font-medium">Sarah Johnson</p>
              <p className="text-sm opacity-80">Director of Customer Experience, TechCorp</p>
            </div>
          </div>
        </motion.div>
      </div>
      
      {/* Right Panel - Form */}
      <div className="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12">
        <div className="w-full max-w-md">
          <div className="text-center mb-10">
            <div className="flex justify-center mb-6 md:hidden">
              <img src="/logo.svg" alt="AI Auto Review" className="h-12" />
            </div>
            <motion.h2 
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
              className="text-2xl font-bold text-gray-900"
            >
              {success ? 'Check Your Email' : 'Reset Your Password'}
            </motion.h2>
            <motion.p 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ duration: 0.5, delay: 0.1 }}
              className="mt-2 text-sm text-gray-600"
            >
              {success 
                ? 'We\'ve sent you instructions to reset your password' 
                : 'Enter your email and we\'ll send you instructions to reset your password'}
            </motion.p>
          </div>
          
          {success ? (
            <motion.div 
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.5 }}
              className="text-center"
            >
              <div className="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                <Check size={32} weight="bold" className="text-green-600" />
              </div>
              
              <p className="text-gray-600 mb-8">
                We've sent a password reset link to <span className="font-medium text-gray-900">{email}</span>. 
                Please check your inbox and follow the instructions to reset your password.
              </p>
              
              <div className="mt-6 flex flex-col space-y-4">
                <Link
                  to="/login"
                  className="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                >
                  <ArrowLeft size={20} className="mr-2" />
                  Return to Login
                </Link>
              </div>
            </motion.div>
          ) : (
            <motion.form 
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.2 }}
              className="space-y-6" 
              onSubmit={handleSubmit}
            >
              {error && (
                <motion.div 
                  initial={{ opacity: 0, height: 0 }}
                  animate={{ opacity: 1, height: 'auto' }}
                  className="rounded-md bg-red-50 p-4"
                >
                  <div className="flex">
                    <div className="flex-shrink-0">
                      {/* Error icon */}
                    </div>
                    <div className="ml-3">
                      <h3 className="text-sm font-medium text-red-800">Error</h3>
                      <div className="mt-2 text-sm text-red-700">
                        <p>{error}</p>
                      </div>
                    </div>
                  </div>
                </motion.div>
              )}
              
              <div>
                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                  Email address
                </label>
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <EnvelopeSimple size={20} className="text-gray-400" />
                  </div>
                  <input
                    id="email"
                    name="email"
                    type="email"
                    autoComplete="email"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors"
                    placeholder="you@example.com"
                  />
                </div>
              </div>
              
              <div>
                <button
                  type="submit"
                  disabled={loading}
                  className={`w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors ${loading ? 'opacity-70 cursor-not-allowed' : ''}`}
                >
                  {loading ? (
                    <>
                      <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Sending...
                    </>
                  ) : 'Send Reset Instructions'}
                </button>
              </div>
              
              <div className="flex items-center justify-center mt-6">
                <Link
                  to="/login"
                  className="text-sm font-medium text-primary-600 hover:text-primary-500 flex items-center transition-colors"
                >
                  <ArrowLeft size={16} className="mr-1" />
                  Back to login
                </Link>
              </div>
            </motion.form>
          )}
        </div>
      </div>
    </div>
  );
}
