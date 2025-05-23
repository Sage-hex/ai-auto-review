import React from 'react';
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import ReviewSimulation from '../components/landing/ReviewSimulation';
import Navbar from '../components/landing/Navbar';
import Footer from '../components/landing/Footer';


export default function LandingPage() {
  useEffect(() => {
    document.title = 'AI Auto Review - Automate Your Review Responses';
  }, []);

  return (
    <div className="bg-white">
      {/* Navigation */}
      <Navbar />

      {/* Hero Section with Animation */}
      <div className="pt-28 pb-20 bg-gradient-to-br from-primary-50 via-white to-blue-50 overflow-hidden">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
          {/* Background decorative elements */}
          <div className="absolute top-0 right-0 -mt-20 -mr-20 hidden lg:block opacity-20">
            <svg width="404" height="404" fill="none" viewBox="0 0 404 404" aria-hidden="true">
              <defs>
                <pattern id="85737c0e-0916-41d7-917f-596dc7edfa27" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                  <rect x="0" y="0" width="4" height="4" className="text-primary-200" fill="currentColor" />
                </pattern>
              </defs>
              <rect width="404" height="404" fill="url(#85737c0e-0916-41d7-917f-596dc7edfa27)" />
            </svg>
          </div>
          <div className="absolute bottom-0 left-0 -mb-20 -ml-20 hidden lg:block opacity-20">
            <svg width="404" height="404" fill="none" viewBox="0 0 404 404" aria-hidden="true">
              <defs>
                <pattern id="85737c0e-0916-41d7-917f-596dc7edfa28" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                  <rect x="0" y="0" width="4" height="4" className="text-blue-200" fill="currentColor" />
                </pattern>
              </defs>
              <rect width="404" height="404" fill="url(#85737c0e-0916-41d7-917f-596dc7edfa28)" />
            </svg>
          </div>
          
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div className="relative z-10">
              <div className="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-primary-100 text-primary-800 mb-6 animate-pulse">
                <span className="flex h-3 w-3 relative mr-3">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-3 w-3 bg-primary-500"></span>
                </span>
                New AI-Powered Platform
              </div>
              <h1 className="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                <span className="block">Automate Your</span>
                <span className="block bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-600">Review Responses</span>
              </h1>
              <p className="mt-6 text-xl text-gray-500 leading-relaxed">
                Save time and delight customers with AI-powered responses to reviews across Google, Yelp, and Facebook. Boost your online reputation effortlessly.
              </p>
              <div className="mt-10 flex flex-wrap gap-4">
                <Link
                  to="/register"
                  className="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-md text-base font-medium text-white bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 transform transition-all duration-200 hover:scale-105"
                >
                  Start Free Trial
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </Link>
                <a
                  href="#features"
                  className="inline-flex items-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 transform transition-all duration-200 hover:scale-105"
                >
                  Learn More
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </a>
              </div>
              
              <div className="mt-8 flex items-center text-gray-500 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                No credit card required
                
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-green-500 mx-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                14-day free trial
                
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-green-500 mx-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                Cancel anytime
              </div>
            </div>
            <div className="relative z-10 transform transition-all duration-500 hover:scale-105">
              <ReviewSimulation />
            </div>
          </div>
        </div>
      </div>

      {/* Features Section */}
      <div id="features" className="py-24 bg-white relative overflow-hidden">
        {/* Background decorative elements */}
        <div className="absolute top-0 right-0 -mt-20 -mr-20 hidden lg:block opacity-10">
          <svg width="404" height="404" fill="none" viewBox="0 0 404 404" aria-hidden="true">
            <defs>
              <pattern id="85737c0e-0916-41d7-917f-596dc7edfa29" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <rect x="0" y="0" width="4" height="4" className="text-gray-200" fill="currentColor" />
              </pattern>
            </defs>
            <rect width="404" height="404" fill="url(#85737c0e-0916-41d7-917f-596dc7edfa29)" />
          </svg>
        </div>
        
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="text-center">
            <span className="bg-primary-100 text-primary-800 text-xs font-semibold px-4 py-1 rounded-full uppercase tracking-wide">Features</span>
            <h2 className="mt-4 text-3xl font-extrabold text-gray-900 sm:text-4xl lg:text-5xl">
              <span className="bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-600">Powerful Features</span> for Your Business
            </h2>
            <p className="mt-4 max-w-2xl text-xl text-gray-500 mx-auto leading-relaxed">
              Everything you need to manage and respond to customer reviews efficiently and effectively.
            </p>
          </div>

          <div className="mt-20 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            {/* Feature 1 */}
            <div className="bg-white rounded-xl shadow-lg p-8 border border-gray-100 transform transition-all duration-300 hover:shadow-xl hover:-translate-y-2 relative overflow-hidden">
              <div className="absolute right-0 top-0 h-16 w-16 bg-gradient-to-bl from-primary-100 to-transparent rounded-bl-full opacity-60"></div>
              <div className="bg-gradient-to-br from-primary-500 to-secondary-600 rounded-lg w-14 h-14 flex items-center justify-center mb-6 shadow-md">
                <svg className="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-3">AI-Powered Responses</h3>
              <p className="text-base text-gray-500 leading-relaxed">
                Generate personalized, contextual responses to reviews in seconds using advanced AI technology. Our system learns from your brand voice and improves over time.
              </p>
              <div className="mt-6 flex items-center text-primary-600 font-medium">
                <span>Learn more</span>
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </div>
            </div>

            {/* Feature 2 */}
            <div className="bg-white rounded-xl shadow-lg p-8 border border-gray-100 transform transition-all duration-300 hover:shadow-xl hover:-translate-y-2 relative overflow-hidden">
              <div className="absolute right-0 top-0 h-16 w-16 bg-gradient-to-bl from-primary-100 to-transparent rounded-bl-full opacity-60"></div>
              <div className="bg-gradient-to-br from-primary-500 to-secondary-600 rounded-lg w-14 h-14 flex items-center justify-center mb-6 shadow-md">
                <svg className="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-3">Multi-Platform Integration</h3>
              <p className="text-base text-gray-500 leading-relaxed">
                Manage reviews from Google, Yelp, and Facebook all in one place with seamless integration. Centralize your review management workflow efficiently.
              </p>
              <div className="mt-6 flex items-center text-primary-600 font-medium">
                <span>Learn more</span>
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </div>
            </div>

            {/* Feature 3 */}
            <div className="bg-white rounded-xl shadow-lg p-8 border border-gray-100 transform transition-all duration-300 hover:shadow-xl hover:-translate-y-2 relative overflow-hidden">
              <div className="absolute right-0 top-0 h-16 w-16 bg-gradient-to-bl from-primary-100 to-transparent rounded-bl-full opacity-60"></div>
              <div className="bg-gradient-to-br from-primary-500 to-secondary-600 rounded-lg w-14 h-14 flex items-center justify-center mb-6 shadow-md">
                <svg className="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-3">Advanced Analytics</h3>
              <p className="text-base text-gray-500 leading-relaxed">
                Gain insights into your review performance with detailed analytics and sentiment analysis. Track trends and improve your business based on customer feedback.
              </p>
              <div className="mt-6 flex items-center text-primary-600 font-medium">
                <span>Learn more</span>
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Pricing Section */}
      <div id="pricing" className="py-24 bg-gradient-to-b from-white to-gray-50 relative overflow-hidden">
        {/* Background decorative elements */}
        <div className="absolute bottom-0 left-0 -mb-20 -ml-20 hidden lg:block opacity-10">
          <svg width="404" height="404" fill="none" viewBox="0 0 404 404" aria-hidden="true">
            <defs>
              <pattern id="pricing-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <rect x="0" y="0" width="4" height="4" className="text-gray-200" fill="currentColor" />
              </pattern>
            </defs>
            <rect width="404" height="404" fill="url(#pricing-pattern)" />
          </svg>
        </div>
        
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="text-center">
            <span className="bg-primary-100 text-primary-800 text-xs font-semibold px-4 py-1 rounded-full uppercase tracking-wide">Pricing</span>
            <h2 className="mt-4 text-3xl font-extrabold text-gray-900 sm:text-4xl lg:text-5xl">
              Simple, <span className="bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-600">Transparent Pricing</span>
            </h2>
            <p className="mt-4 max-w-2xl text-xl text-gray-500 mx-auto leading-relaxed">
              Choose the plan that's right for your business. All plans include a 14-day free trial.
            </p>
          </div>

          <div className="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
            {/* Free Plan */}
            <div className="bg-white rounded-xl shadow-lg p-8 border border-gray-100 relative overflow-hidden transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
              <div className="absolute right-0 top-0 h-24 w-24 bg-gradient-to-bl from-gray-100 to-transparent rounded-bl-full opacity-60"></div>
              
              <div className="flex items-center mb-6">
                <div className="bg-gray-100 rounded-full w-12 h-12 flex items-center justify-center mr-4 shadow-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <h3 className="text-xl font-bold text-gray-900">Free Plan</h3>
              </div>
              
              <div className="mb-6 pb-6 border-b border-gray-100">
                <p className="text-4xl font-extrabold text-gray-900 flex items-baseline">
                  $0<span className="text-xl font-medium text-gray-500 ml-1">/month</span>
                </p>
                <p className="mt-4 text-gray-500 leading-relaxed">
                  Perfect for small businesses just getting started with review management.
                </p>
              </div>
              
              <ul className="mb-8 space-y-4">
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">2 user accounts</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">Google My Business integration</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">50 AI responses/month</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">Basic analytics</span>
                </li>
              </ul>
              
              <Link
                to="/register"
                className="w-full flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200 transform hover:scale-105"
              >
                Start for Free
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </Link>
            </div>

            {/* Basic Plan */}
            {/* <div className="bg-white rounded-xl shadow-xl p-8 border-2 border-primary-500 relative overflow-hidden transform transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 z-10">
              <div className="absolute right-0 top-0 h-24 w-24 bg-gradient-to-bl from-primary-100 to-transparent rounded-bl-full opacity-60"></div>
              
              <div className="absolute top-0 inset-x-0 transform -translate-y-1/2 flex justify-center">
                <div className="px-4 py-1 bg-gradient-to-r from-primary-600 to-secondary-600 rounded-full text-sm font-semibold uppercase tracking-wider text-white shadow-md">
                  Most Popular
                </div>
              </div>
              
              <div className="flex items-center justify-center mb-6 mt-2">
                <div className="bg-gradient-to-br from-primary-500 to-secondary-600 rounded-full w-12 h-12 flex items-center justify-center mr-4 shadow-md">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <h3 className="text-xl font-bold text-gray-900">Basic Plan</h3>
              </div>
              
              <div className="mb-6 pb-6 border-b border-gray-100 text-center">
                <p className="text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-600 flex items-baseline justify-center">
                  $29<span className="text-xl font-medium text-gray-500 ml-1">/month</span>
                </p>
                <p className="mt-4 text-gray-500 leading-relaxed">
                  Great for growing businesses with multiple review platforms.
                </p>
              </div>
              
              <ul className="mb-8 space-y-4 mx-auto text-left max-w-xs">
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">5 user accounts</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">Google & Yelp integrations</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">200 AI responses/month</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">Advanced analytics & reports</span>
                </li>
              </ul>
              
              <div className="text-center">
                <Link
                  to="/register"
                  className="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg shadow-md text-base font-medium text-white bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 transition-all duration-200 transform hover:scale-105"
                >
                  Start 14-Day Free Trial
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </Link>
              </div>
            </div> */}

            <div className="bg-white rounded-xl shadow-xl p-8 border-2 border-primary-500 relative overflow-hidden transform transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 z-10">
  <div className="absolute right-0 top-0 h-24 w-24 bg-gradient-to-bl from-primary-100 to-transparent rounded-bl-full opacity-60"></div>

  <div className="absolute top-0 right-0 transform translate-y-2 translate-x-2">
    <div className="px-4 py-1 bg-gradient-to-r from-primary-600 to-secondary-600 rounded-full text-sm font-semibold uppercase tracking-wider text-white shadow-md">
      Most Popular
    </div>
  </div>

  <div className="flex items-center justify-center mb-6 mt-2">
    <div className="bg-gradient-to-br from-primary-500 to-secondary-600 rounded-full w-12 h-12 flex items-center justify-center mr-4 shadow-md">
      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>
    </div>
    <h3 className="text-xl font-bold text-gray-900">Basic Plan</h3>
  </div>

  <div className="mb-6 pb-6 border-b border-gray-100 text-center">
    <p className="text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-600 flex items-baseline justify-center">
      $29<span className="text-xl font-medium text-gray-500 ml-1">/month</span>
    </p>
    <p className="mt-4 text-gray-500 leading-relaxed">
      Great for growing businesses with multiple review platforms.
    </p>
  </div>

  <ul className="mb-8 space-y-4 mx-auto text-left max-w-xs">
    <li className="flex items-start">
      <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
      </svg>
      <span className="ml-3 text-gray-500">5 user accounts</span>
    </li>
    <li className="flex items-start">
      <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
      </svg>
      <span className="ml-3 text-gray-500">Google & Yelp integrations</span>
    </li>
    <li className="flex items-start">
      <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
      </svg>
      <span className="ml-3 text-gray-500">200 AI responses/month</span>
    </li>
    <li className="flex items-start">
      <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
      </svg>
      <span className="ml-3 text-gray-500">Advanced analytics & reports</span>
    </li>
  </ul>

  <div className="text-center">
    <Link
      to="/register"
      className="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg shadow-md text-base font-medium text-white bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 transition-all duration-200 transform hover:scale-105"
    >
      Start 14-Day Free Trial
      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
        <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
      </svg>
    </Link>
  </div>
</div>


            {/* Pro Plan */}
            <div className="bg-white rounded-xl shadow-lg p-8 border border-gray-100 relative overflow-hidden transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
              <div className="absolute right-0 top-0 h-24 w-24 bg-gradient-to-bl from-secondary-100 to-transparent rounded-bl-full opacity-60"></div>
              
              <div className="flex items-center mb-6">
                <div className="bg-gradient-to-br from-secondary-500 to-primary-600 rounded-full w-12 h-12 flex items-center justify-center mr-4 shadow-md">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                  </svg>
                </div>
                <h3 className="text-xl font-bold text-gray-900">Pro Plan</h3>
              </div>
              
              <div className="mb-6 pb-6 border-b border-gray-100">
                <p className="text-4xl font-extrabold text-gray-900 flex items-baseline">
                  $79<span className="text-xl font-medium text-gray-500 ml-1">/month</span>
                </p>
                <p className="mt-4 text-gray-500 leading-relaxed">
                  For businesses that need the full suite of features and support.
                </p>
              </div>
              
              <ul className="mb-8 space-y-4">
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">15 user accounts</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">Google, Yelp & Facebook integrations</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">Unlimited AI responses</span>
                </li>
                <li className="flex items-start">
                  <svg className="h-5 w-5 text-green-500 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span className="ml-3 text-gray-500">Premium support & custom training</span>
                </li>
              </ul>
              
              <Link
                to="/register"
                className="w-full flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200 transform hover:scale-105"
              >
                Start 14-Day Free Trial
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </Link>
            </div>
          </div>
        </div>
      </div>

      {/* CTA Section */}
      <div className="bg-gradient-to-br from-primary-600 to-secondary-700 relative overflow-hidden">
        {/* Background decorative elements */}
        <div className="absolute inset-0 opacity-10">
          <svg className="h-full w-full" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <pattern id="cta-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <rect x="0" y="0" width="4" height="4" fill="white" />
              </pattern>
            </defs>
            <rect width="1000" height="1000" fill="url(#cta-pattern)" />
          </svg>
        </div>
        
        <div className="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:py-24 lg:px-8 relative z-10">
          <div className="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
            <div>
              <h2 className="text-3xl font-extrabold text-white sm:text-4xl">
                <span className="block">Ready to transform your</span>
                <span className="block text-primary-200">review management process?</span>
              </h2>
              <p className="mt-4 text-lg text-primary-100 leading-relaxed">
                Join thousands of businesses that are saving time and delighting customers with AI-powered review responses. Our 14-day free trial gives you full access to all features with no credit card required.  
              </p>
              
              <div className="mt-8 flex flex-wrap gap-4">
                <Link
                  to="/register"
                  className="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg shadow-lg text-base font-medium text-primary-700 bg-white hover:bg-primary-50 transition-all duration-200 transform hover:scale-105"
                >
                  Start Free Trial
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </Link>
                <a
                  href="#features"
                  className="inline-flex items-center justify-center px-6 py-3 border border-primary-300 rounded-lg shadow-lg text-base font-medium text-white hover:bg-primary-500 transition-all duration-200 transform hover:scale-105"
                >
                  Learn More
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </a>
              </div>
              
              <div className="mt-6 flex items-center text-primary-100 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                No credit card required
              </div>
            </div>
            
            <div className="mt-10 lg:mt-0 relative">
              <div className="bg-white rounded-xl shadow-2xl p-6 transform transition-all duration-500 hover:scale-105">
                <div className="flex items-center mb-4">
                  <div className="bg-gradient-to-br from-primary-500 to-secondary-600 rounded-full w-10 h-10 flex items-center justify-center mr-3 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                    </svg>
                  </div>
                  <h3 className="text-lg font-bold text-gray-900">What our customers say</h3>
                </div>
                
                <blockquote className="italic text-gray-700 mb-4 leading-relaxed">
                  "AI Auto Review has completely transformed how we handle customer feedback. Our response time has decreased by 90% and customer satisfaction has increased significantly. The AI responses are personalized and on-brand."
                </blockquote>
                
                <div className="flex items-center">
                  <div className="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                    <span className="text-gray-600 font-medium">JD</span>
                  </div>
                  <div>
                    <p className="text-sm font-medium text-gray-900">Jane Doe</p>
                    <p className="text-xs text-gray-500">Marketing Director, Acme Inc.</p>
                  </div>
                </div>
              </div>
              
              <div className="absolute -top-6 -right-6 h-24 w-24 bg-yellow-400 rounded-full opacity-50 blur-xl"></div>
              <div className="absolute -bottom-10 -left-10 h-32 w-32 bg-primary-400 rounded-full opacity-40 blur-xl"></div>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <Footer />
    </div>
  );
}
