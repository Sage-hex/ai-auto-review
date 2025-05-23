import { useEffect } from 'react';
import { motion } from 'framer-motion';

export default function ResponsesPage() {
  useEffect(() => {
    document.title = 'Responses | AI Auto Review';
  }, []);

  return (
    <>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Response Templates</h1>
        <div className="flex space-x-2">
          <button
            className="flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Create Template
          </button>
        </div>
      </div>
      
      <div className="bg-white shadow rounded-lg">
        <div className="p-6">
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
          >
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {/* Template Card */}
              {[1, 2, 3].map((item) => (
                <div key={item} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                  <h3 className="font-semibold text-lg mb-2">Positive Review Response</h3>
                  <p className="text-gray-600 text-sm mb-4 line-clamp-3">
                    Thank you so much for your kind words! We're thrilled to hear you had such a positive experience with us. Your feedback means a lot to our team.
                  </p>
                  <div className="flex justify-between items-center">
                    <span className="text-xs text-gray-500">Used 24 times</span>
                    <div className="flex space-x-2">
                      <button className="text-gray-500 hover:text-primary-600">Edit</button>
                      <button className="text-gray-500 hover:text-red-600">Delete</button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </motion.div>
        </div>
      </div>
    </>
  );
}
