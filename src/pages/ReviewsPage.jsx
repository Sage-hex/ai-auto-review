import { useEffect } from 'react';
import { useReviews } from '../contexts/ReviewContext';
import ReviewList from '../components/reviews/ReviewList';

export default function ReviewsPage() {
  useEffect(() => {
    document.title = 'Reviews | AI Auto Review';
  }, []);

  return <ReviewList />;
}
