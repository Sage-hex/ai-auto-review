import { RouterProvider } from 'react-router-dom';
import axios from 'axios';
import './App.css';

// Import router from router.jsx
import { router } from './router.jsx';

// Import context providers
import { AuthProvider } from './contexts/AuthContext';
import { ReviewProvider } from './contexts/ReviewContext';
import RouteTransition from './components/common/RouteTransition';

// Set base URL for API requests
axios.defaults.baseURL = 'http://localhost/AiAutoReview';

function App() {
  return (
    <AuthProvider>
      <ReviewProvider>
        <RouterProvider router={router} />
      </ReviewProvider>
    </AuthProvider>
  );
}

export default App
