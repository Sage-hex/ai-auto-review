import { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { motion } from 'framer-motion';
import { Check, X, CurrencyCircleDollar, Rocket, Crown } from 'phosphor-react';

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

// Mock subscription plans
const MOCK_PLANS = [
  {
    id: 'starter',
    name: 'Starter',
    description: 'Perfect for small businesses just getting started with review management',
    price: 29,
    icon: <CurrencyCircleDollar size={32} weight="fill" className="text-primary-500" />,
    features: [
      { name: 'Up to 100 reviews per month', included: true },
      { name: 'Basic AI response generation', included: true },
      { name: 'Single platform integration', included: true },
      { name: 'Email notifications', included: true },
      { name: 'Basic analytics', included: true },
      { name: 'Advanced sentiment analysis', included: false },
      { name: 'Custom response templates', included: false },
      { name: 'Multi-user access', included: false },
      { name: 'Priority support', included: false },
    ],
    popular: false,
    buttonText: 'Start with Starter',
    buttonColor: 'bg-primary-600 hover:bg-primary-700'
  },
  {
    id: 'professional',
    name: 'Professional',
    description: 'Ideal for growing businesses with multiple review sources',
    price: 79,
    icon: <Rocket size={32} weight="fill" className="text-indigo-500" />,
    features: [
      { name: 'Up to 500 reviews per month', included: true },
      { name: 'Advanced AI response generation', included: true },
      { name: 'Multiple platform integrations', included: true },
      { name: 'Real-time notifications', included: true },
      { name: 'Comprehensive analytics', included: true },
      { name: 'Advanced sentiment analysis', included: true },
      { name: 'Custom response templates', included: true },
      { name: 'Multi-user access (up to 5)', included: true },
      { name: 'Priority support', included: false },
    ],
    popular: true,
    buttonText: 'Upgrade to Professional',
    buttonColor: 'bg-indigo-600 hover:bg-indigo-700'
  },
  {
    id: 'enterprise',
    name: 'Enterprise',
    description: 'Complete solution for large businesses with high review volume',
    price: 199,
    icon: <Crown size={32} weight="fill" className="text-amber-500" />,
    features: [
      { name: 'Unlimited reviews', included: true },
      { name: 'Premium AI response generation', included: true },
      { name: 'All platform integrations', included: true },
      { name: 'Custom notification system', included: true },
      { name: 'Enterprise-grade analytics', included: true },
      { name: 'Advanced sentiment analysis', included: true },
      { name: 'Custom response templates library', included: true },
      { name: 'Unlimited user access', included: true },
      { name: 'Dedicated account manager', included: true },
    ],
    popular: false,
    buttonText: 'Contact Sales',
    buttonColor: 'bg-amber-600 hover:bg-amber-700'
  }
];

export default function SubscriptionPlans() {
  const { currentUser, business } = useAuth();
  const [plans, setPlans] = useState(MOCK_PLANS);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [processingPlan, setProcessingPlan] = useState(null);
  
  // Check if user has admin privileges
  const isAdmin = currentUser?.role === 'admin';
  
  useEffect(() => {
    // In a real app, we would fetch plans from the API
    // For now, we're using mock data
    setLoading(true);
    setTimeout(() => {
      setPlans(MOCK_PLANS);
      setLoading(false);
    }, 1000);
  }, []);
  
  const handleChangePlan = async (planId) => {
    if (!isAdmin) return;
    
    try {
      setProcessingPlan(planId);
      setError('');
      
      // Simulate API call to change subscription plan
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      // Show success message or redirect
      alert(`Successfully upgraded to ${plans.find(p => p.id === planId).name} plan!`);
    } catch (err) {
      console.error('Error changing subscription plan:', err);
      setError('Failed to change subscription plan. Please try again.');
    } finally {
      setProcessingPlan(null);
    }
  };
  
  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-primary-600"></div>
      </div>
    );
  }
  
  if (error) {
    return (
      <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <div className="flex items-center">
          <div className="flex-shrink-0">
            <X weight="bold" className="h-5 w-5 text-red-500" />
          </div>
          <div className="ml-3">
            <p className="text-red-700">{error}</p>
          </div>
        </div>
      </div>
    );
  }
  
  return (
    <>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Subscription Plans</h1>
      </div>
      
      <motion.div 
        className="grid grid-cols-1 lg:grid-cols-3 gap-8"
        variants={containerVariants}
        initial="hidden"
        animate="visible"
      >
        {plans.map(plan => (
          <motion.div 
            key={plan.id} 
            variants={itemVariants}
            className={`relative bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl border ${plan.popular ? 'border-indigo-500 ring-2 ring-indigo-500 ring-opacity-50' : 'border-gray-200'}`}
          >
            {plan.popular && (
              <div className="absolute top-0 right-0 bg-indigo-500 text-white px-4 py-1 rounded-bl-lg text-sm font-medium">
                Popular
              </div>
            )}
            
            <div className="p-6">
              <div className="flex items-center mb-4">
                {plan.icon}
                <h2 className="text-xl font-bold ml-2">{plan.name}</h2>
              </div>
              
              <p className="text-gray-600 mb-4 h-12">{plan.description}</p>
              
              <div className="flex items-baseline mb-6">
                <span className="text-4xl font-extrabold">${plan.price}</span>
                <span className="text-gray-500 ml-1">/month</span>
              </div>
              
              <div className="border-t border-gray-200 pt-4 pb-6">
                <ul className="space-y-3">
                  {plan.features.map((feature, index) => (
                    <li key={index} className="flex items-start">
                      {feature.included ? (
                        <Check weight="bold" className="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5 mr-2" />
                      ) : (
                        <X weight="bold" className="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5 mr-2" />
                      )}
                      <span className={feature.included ? 'text-gray-800' : 'text-gray-400'}>{feature.name}</span>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
            
            <div className="px-6 pb-6">
              <button
                onClick={() => handleChangePlan(plan.id)}
                disabled={processingPlan === plan.id}
                className={`w-full py-3 px-4 rounded-lg text-white font-semibold transition-colors duration-200 ${processingPlan === plan.id ? 'bg-gray-400' : plan.buttonColor}`}
              >
                {processingPlan === plan.id ? (
                  <div className="flex items-center justify-center">
                    <div className="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-white mr-2"></div>
                    Processing...
                  </div>
                ) : plan.buttonText}
              </button>
            </div>
          </motion.div>
        ))}
      </motion.div>
    </>
  );
}
