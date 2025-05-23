import { useState, useEffect, useRef } from 'react';

export default function ReviewSimulation() {
  const [reviewText, setReviewText] = useState('');
  const [responseText, setResponseText] = useState('');
  const [isTypingReview, setIsTypingReview] = useState(false);
  const [isProcessing, setIsProcessing] = useState(false);
  const [isTypingResponse, setIsTypingResponse] = useState(false);
  const [showCursor, setShowCursor] = useState(true);
  
  const fullReviewText = "I visited this restaurant last weekend and the food was amazing! The service was a bit slow though, but overall a good experience.";
  const fullResponseText = "Thank you so much for your kind words about our food! We're thrilled you enjoyed your meal. We apologize for the slow service and we're working on improving that aspect of your experience. We hope to welcome you back soon!";
  
  const reviewIndex = useRef(0);
  const responseIndex = useRef(0);
  const typingSpeed = 50; // milliseconds per character
  
  // Blinking cursor effect
  useEffect(() => {
    const cursorInterval = setInterval(() => {
      setShowCursor(prev => !prev);
    }, 500);
    
    return () => clearInterval(cursorInterval);
  }, []);
  
  // Animation sequence
  useEffect(() => {
    const startAnimation = async () => {
      // Wait a moment before starting
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Start typing the review
      setIsTypingReview(true);
      
      // Wait for review typing to complete
      await new Promise(resolve => {
        const interval = setInterval(() => {
          if (reviewIndex.current < fullReviewText.length) {
            setReviewText(fullReviewText.substring(0, reviewIndex.current + 1));
            reviewIndex.current += 1;
          } else {
            clearInterval(interval);
            setIsTypingReview(false);
            resolve();
          }
        }, typingSpeed);
      });
      
      // Wait a moment after review is typed
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Start AI processing
      setIsProcessing(true);
      
      // Simulate AI thinking time
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Stop processing, start typing response
      setIsProcessing(false);
      setIsTypingResponse(true);
      
      // Type out the response
      await new Promise(resolve => {
        const interval = setInterval(() => {
          if (responseIndex.current < fullResponseText.length) {
            setResponseText(fullResponseText.substring(0, responseIndex.current + 1));
            responseIndex.current += 1;
          } else {
            clearInterval(interval);
            setIsTypingResponse(false);
            resolve();
          }
        }, typingSpeed);
      });
      
      // Wait before restarting the animation
      await new Promise(resolve => setTimeout(resolve, 5000));
      
      // Reset and restart
      setReviewText('');
      setResponseText('');
      reviewIndex.current = 0;
      responseIndex.current = 0;
      startAnimation();
    };
    
    startAnimation();
    
    return () => {
      // Cleanup if component unmounts
      reviewIndex.current = 0;
      responseIndex.current = 0;
    };
  }, []);

  return (
    <div className="w-full max-w-2xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden transform transition-all duration-300 hover:shadow-xl">
      <div className="p-6">
        <h3 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
          <span className="bg-gradient-to-r from-primary-500 to-secondary-600 w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </span>
          See AI Auto Review in Action
        </h3>
        
        {/* Customer Review Section */}
        <div className="mb-6 transform transition-all duration-300 hover:translate-x-1">
          <div className="flex items-center mb-2">
            <div className="bg-gray-200 rounded-full h-8 w-8 flex items-center justify-center mr-2 shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <span className="text-sm font-medium text-gray-700 flex items-center">
              Customer Review
              <span className="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs rounded-full">Google</span>
            </span>
          </div>
          <div className="bg-gray-100 rounded-lg p-4 border-l-4 border-yellow-400 shadow-sm">
            <div className="flex items-center mb-2">
              <div className="flex text-yellow-400">
                {[...Array(5)].map((_, i) => (
                  <svg key={i} xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                ))}
              </div>
              <span className="ml-2 text-xs text-gray-500">2 days ago</span>
            </div>
            <p className="text-gray-800 font-medium">
              {reviewText}
              {isTypingReview && showCursor && <span className="animate-pulse text-primary-600">|</span>}
            </p>
          </div>
        </div>
        
        {/* AI Processing Indicator */}
        {isProcessing && (
          <div className="flex justify-center my-6">
            <div className="bg-primary-50 rounded-full px-4 py-2 shadow-sm flex items-center">
              <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span className="text-sm font-medium text-primary-700">AI generating response...</span>
            </div>
          </div>
        )}
        
        {/* AI Response Section */}
        <div className={`transform transition-all duration-500 ${responseText ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
          <div className="flex items-center mb-2">
            <div className="bg-gradient-to-r from-primary-500 to-secondary-600 rounded-full h-8 w-8 flex items-center justify-center mr-2 shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <span className="text-sm font-medium text-gray-700 flex items-center">
              AI-Generated Response
              <span className="ml-2 px-2 py-0.5 bg-primary-100 text-primary-800 text-xs rounded-full">Auto Review</span>
            </span>
          </div>
          <div className="bg-gradient-to-r from-primary-50 to-blue-50 border border-primary-100 rounded-lg p-4 shadow-sm">
            <p className="text-gray-800 font-medium">
              {responseText}
              {isTypingResponse && showCursor && <span className="animate-pulse text-primary-600">|</span>}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
