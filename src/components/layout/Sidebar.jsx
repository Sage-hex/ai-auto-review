import { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';

// Premium Phosphor Icons
import { 
  House, 
  Star, 
  Users, 
  Gear, 
  CreditCard, 
  SignOut, 
  List, 
  X,
  ChartLine,
  ChatCircleText,
  Buildings
} from 'phosphor-react';

export default function Sidebar() {
  const { currentUser, business, logout } = useAuth();
  const location = useLocation();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [showNotifications, setShowNotifications] = useState(false);

  // Check if user has required role for a menu item
  const hasRequiredRole = (requiredRoles) => {
    if (!requiredRoles || requiredRoles.length === 0) return true;
    return requiredRoles.includes(currentUser?.role);
  };

  const menuItems = [
    {
      name: 'Dashboard',
      path: '/dashboard',
      icon: <House weight="duotone" className="w-6 h-6" />,
      roles: ['admin', 'manager', 'support', 'viewer']
    },
    {
      name: 'Reviews',
      path: '/reviews',
      icon: <Star weight="duotone" className="w-6 h-6" />,
      roles: ['admin', 'manager', 'support', 'viewer']
    },
    {
      name: 'Analytics',
      path: '/analytics',
      icon: <ChartLine weight="duotone" className="w-6 h-6" />,
      roles: ['admin', 'manager']
    },
    {
      name: 'Users',
      path: '/users',
      icon: <Users weight="duotone" className="w-6 h-6" />,
      roles: ['admin', 'manager']
    },
    {
      name: 'Platforms',
      path: '/platforms',
      icon: <Buildings weight="duotone" className="w-6 h-6" />,
      roles: ['admin', 'manager']
    },
    {
      name: 'Responses',
      path: '/responses',
      icon: <ChatCircleText weight="duotone" className="w-6 h-6" />,
      roles: ['admin', 'manager', 'support']
    },
    {
      name: 'Settings',
      path: '/settings',
      icon: <Gear weight="duotone" className="w-6 h-6" />,
      roles: ['admin', 'manager']
    },
    {
      name: 'Subscription',
      path: '/subscription',
      icon: <CreditCard weight="duotone" className="w-6 h-6" />,
      roles: ['admin']
    }
  ];

  const toggleMobileMenu = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen);
  };

  const handleLogout = () => {
    logout();
  };

  return (
    <>
      {/* Mobile menu button and header bar */}
      <div className="md:hidden fixed top-0 left-0 right-0 z-50 flex items-center justify-between bg-white border-b border-neutral-200 shadow-sm px-4 py-3">
        <div className="flex items-center">
          <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center shadow-md mr-2">
            <ChatCircleText weight="fill" className="h-4 w-4 text-white" />
          </div>
          <h1 className="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-700">AI Auto Review</h1>
        </div>
        <button
          onClick={toggleMobileMenu}
          className="text-primary-600 hover:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-full p-1"
          aria-label="Toggle menu"
        >
          {isMobileMenuOpen ? (
            <X weight="bold" className="w-6 h-6" />
          ) : (
            <List weight="bold" className="w-6 h-6" />
          )}
        </button>
      </div>

      {/* Sidebar for desktop */}
      <div className="hidden md:flex md:flex-col md:w-64 md:fixed md:inset-y-0 bg-white border-r border-gray-200">
        <div className="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
          <div className="flex items-center flex-shrink-0 px-4">
            <div className="flex items-center">
              <div className="h-9 w-9 rounded-lg bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center shadow-md mr-3">
                <ChatCircleText weight="fill" className="h-5 w-5 text-white" />
              </div>
              <h1 className="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-700">AI Auto Review</h1>
            </div>
          </div>
          
          {/* Business info */}
          <div className="mt-5 px-4">
            <div className="bg-gradient-to-r from-primary-50 to-secondary-50 border border-primary-100 rounded-lg p-4 shadow-sm">
              <p className="text-sm font-semibold text-primary-800">{business?.name}</p>
              <div className="mt-1 flex items-center">
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 capitalize">
                  {business?.subscription_plan}
                </span>
              </div>
            </div>
          </div>
          
          {/* Navigation */}
          <nav className="mt-5 flex-1 px-2 space-y-1">
            {menuItems.map((item) => 
              hasRequiredRole(item.roles) && (
                <Link
                  key={item.path}
                  to={item.path}
                  className={`group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ${
                    location.pathname === item.path
                      ? 'bg-primary-100 text-primary-700 shadow-sm'
                      : 'text-neutral-700 hover:bg-neutral-100 hover:text-primary-600'
                  }`}
                >
                  <div className={`mr-3 transition-colors duration-200 ${
                    location.pathname === item.path
                      ? 'text-primary-600'
                      : 'text-neutral-500 group-hover:text-primary-500'
                  }`}>
                    {item.icon}
                  </div>
                  {item.name}
                </Link>
              )
            )}
          </nav>
        </div>
        
        {/* User info and logout */}
        <div className="flex-shrink-0 flex border-t border-gray-200 p-4">
          <div className="flex-shrink-0 w-full group block">
            <div className="flex items-center">
              <div className="ml-3">
                <p className="text-sm font-medium text-gray-700">{currentUser?.name}</p>
                <p className="text-xs font-medium text-gray-500 capitalize">
                  {currentUser?.role}
                </p>
              </div>
            </div>
            <button
              onClick={handleLogout}
              className="mt-3 w-full flex items-center text-sm font-medium text-error hover:text-red-700 bg-red-50 hover:bg-red-100 py-2 px-3 rounded-lg transition-colors duration-200"
            >
              <SignOut weight="bold" className="w-5 h-5 mr-2" />
              Sign Out
            </button>
          </div>
        </div>
      </div>

      {/* Mobile menu overlay */}
      <div 
        className={`md:hidden fixed inset-0 bg-neutral-900/50 backdrop-blur-sm transition-opacity duration-300 z-40 ${isMobileMenuOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
        onClick={() => setIsMobileMenuOpen(false)}
        aria-hidden="true"
      ></div>
      
      {/* Mobile menu */}
      <div 
        className={`md:hidden fixed inset-y-0 left-0 w-full max-w-xs bg-white shadow-xl z-50 transform transition-transform duration-300 ease-in-out ${isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'}`}
      >
        <div className="pt-16 pb-4 px-4 h-full overflow-y-auto">
          <div className="flex items-center flex-shrink-0 mb-5">
            <div className="flex items-center">
              <div className="h-9 w-9 rounded-lg bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center shadow-md mr-3">
                <ChatCircleText weight="fill" className="h-5 w-5 text-white" />
              </div>
              <h1 className="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-700">AI Auto Review</h1>
            </div>
          </div>
          
          {/* Business info */}
          <div className="mb-5">
            <div className="bg-gradient-to-r from-primary-50 to-secondary-50 border border-primary-100 rounded-lg p-4 shadow-sm">
              <p className="text-sm font-semibold text-primary-800">{business?.name}</p>
              <div className="mt-1 flex items-center">
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 capitalize">
                  {business?.subscription_plan}
                </span>
              </div>
            </div>
          </div>
          
          {/* Navigation */}
          <nav className="space-y-2">
            {menuItems.map((item) => 
              hasRequiredRole(item.roles) && (
                <Link
                  key={item.path}
                  to={item.path}
                  className={`group flex items-center px-4 py-3.5 text-base font-medium rounded-lg transition-all duration-200 ${
                    location.pathname === item.path
                      ? 'bg-primary-100 text-primary-700 shadow-sm'
                      : 'text-neutral-700 hover:bg-neutral-100 hover:text-primary-600'
                  }`}
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  <div className={`mr-4 transition-colors duration-200 ${
                    location.pathname === item.path
                      ? 'text-primary-600'
                      : 'text-neutral-500 group-hover:text-primary-500'
                  }`}>
                    {item.icon}
                  </div>
                  {item.name}
                </Link>
              )
            )}
          </nav>
          
          {/* User info and logout */}
          <div className="mt-6 pt-6 border-t border-gray-200">
            <div className="flex items-center mb-4">
              <div>
                <p className="text-sm font-medium text-gray-700">{currentUser?.name}</p>
                <p className="text-xs font-medium text-gray-500 capitalize">
                  {currentUser?.role}
                </p>
              </div>
            </div>
            <button
              onClick={handleLogout}
              className="w-full flex items-center px-4 py-3 text-base font-medium text-error hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors duration-200"
            >
              <SignOut weight="bold" className="w-5 h-5 mr-3" />
              Sign Out
            </button>
          </div>
        </div>
      </div>
    </>
  );
}
