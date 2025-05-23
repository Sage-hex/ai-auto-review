import React from 'react';
import { Link } from 'react-router-dom';
import { 
  ChatCircleText, 
  EnvelopeSimple, 
  Phone, 
  MapPin, 
  FacebookLogo, 
  TwitterLogo, 
  LinkedinLogo, 
  InstagramLogo 
} from 'phosphor-react';

export default function Footer() {
  const currentYear = new Date().getFullYear();
  
  return (
    <footer className="bg-white border-t border-gray-200">
      <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Company Info */}
          <div className="col-span-1 md:col-span-1">
            <div className="flex items-center mb-4">
              <div className="h-9 w-9 rounded-lg bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center shadow-md mr-3">
                <ChatCircleText weight="fill" className="h-5 w-5 text-white" />
              </div>
              <span className="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-700">
                AI Auto Review
              </span>
            </div>
            <p className="text-gray-500 text-sm mt-2 mb-4">
              Automate your review responses with AI-powered technology. Save time and delight your customers.
            </p>
            <div className="flex space-x-4">
              <a href="#" className="text-gray-400 hover:text-primary-600 transition-colors">
                <FacebookLogo weight="fill" className="h-5 w-5" />
              </a>
              <a href="#" className="text-gray-400 hover:text-primary-600 transition-colors">
                <TwitterLogo weight="fill" className="h-5 w-5" />
              </a>
              <a href="#" className="text-gray-400 hover:text-primary-600 transition-colors">
                <LinkedinLogo weight="fill" className="h-5 w-5" />
              </a>
              <a href="#" className="text-gray-400 hover:text-primary-600 transition-colors">
                <InstagramLogo weight="fill" className="h-5 w-5" />
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div className="col-span-1">
            <h3 className="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
              Quick Links
            </h3>
            <ul className="space-y-3">
              <li>
                <a href="#features" className="text-gray-500 hover:text-primary-600 transition-colors">
                  Features
                </a>
              </li>
              <li>
                <a href="#pricing" className="text-gray-500 hover:text-primary-600 transition-colors">
                  Pricing
                </a>
              </li>
              <li>
                <a href="#testimonials" className="text-gray-500 hover:text-primary-600 transition-colors">
                  Testimonials
                </a>
              </li>
              <li>
                <Link to="/login" className="text-gray-500 hover:text-primary-600 transition-colors">
                  Login
                </Link>
              </li>
              <li>
                <Link to="/register" className="text-gray-500 hover:text-primary-600 transition-colors">
                  Sign Up
                </Link>
              </li>
            </ul>
          </div>

          {/* Legal */}
          <div className="col-span-1">
            <h3 className="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
              Legal
            </h3>
            <ul className="space-y-3">
              <li>
                <Link to="/privacy" className="text-gray-500 hover:text-primary-600 transition-colors">
                  Privacy Policy
                </Link>
              </li>
              <li>
                <Link to="/terms" className="text-gray-500 hover:text-primary-600 transition-colors">
                  Terms of Service
                </Link>
              </li>
              <li>
                <Link to="/cookies" className="text-gray-500 hover:text-primary-600 transition-colors">
                  Cookie Policy
                </Link>
              </li>
              <li>
                <Link to="/gdpr" className="text-gray-500 hover:text-primary-600 transition-colors">
                  GDPR Compliance
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact */}
          <div className="col-span-1">
            <h3 className="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
              Contact Us
            </h3>
            <ul className="space-y-3">
              <li className="flex items-start">
                <EnvelopeSimple className="h-5 w-5 text-gray-400 mt-0.5 mr-2" />
                <a href="mailto:support@aiautoreview.com" className="text-gray-500 hover:text-primary-600 transition-colors">
                  support@aiautoreview.com
                </a>
              </li>
              <li className="flex items-start">
                <Phone className="h-5 w-5 text-gray-400 mt-0.5 mr-2" />
                <a href="tel:+1-555-123-4567" className="text-gray-500 hover:text-primary-600 transition-colors">
                  +1-555-123-4567
                </a>
              </li>
              <li className="flex items-start">
                <MapPin className="h-5 w-5 text-gray-400 mt-0.5 mr-2" />
                <span className="text-gray-500">
                  123 AI Street, San Francisco, CA 94103
                </span>
              </li>
            </ul>
          </div>
        </div>

        <div className="mt-12 pt-8 border-t border-gray-200">
          <p className="text-center text-gray-500 text-sm">
            &copy; {currentYear} AI Auto Review. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
}
