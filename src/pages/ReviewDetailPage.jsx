import { useEffect } from 'react';
import { useParams } from 'react-router-dom';
import Layout from '../components/layout/Layout';
import ReviewDetail from '../components/reviews/ReviewDetail';

export default function ReviewDetailPage() {
  const { id } = useParams();
  
  useEffect(() => {
    document.title = 'Review Details | AI Auto Review';
  }, []);

  return (
    <Layout>
      <ReviewDetail />
    </Layout>
  );
}
