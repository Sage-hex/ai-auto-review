import { useEffect } from 'react';
import { useReviews } from '../contexts/ReviewContext';
import Layout from '../components/layout/Layout';
import ReviewList from '../components/reviews/ReviewList';

export default function ReviewsPage() {
  useEffect(() => {
    document.title = 'Reviews | AI Auto Review';
  }, []);

  return (
    <Layout>
      <ReviewList />
    </Layout>
  );
}
