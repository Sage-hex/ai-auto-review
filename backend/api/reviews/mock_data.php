<?php
/**
 * Mock data for reviews endpoints
 */

// Mock reviews
function getMockReviews($page = 1) {
    return [
        'success' => true,
        'data' => [
            'reviews' => [
                [
                    'id' => 1,
                    'platform' => 'Google',
                    'rating' => 5,
                    'content' => 'Excellent service! I love this business.',
                    'customer_name' => 'John Smith',
                    'date' => '2023-05-15',
                    'status' => 'published',
                    'response' => 'Thank you for your kind words!'
                ],
                [
                    'id' => 2,
                    'platform' => 'Yelp',
                    'rating' => 4,
                    'content' => 'Great experience overall. Would recommend.',
                    'customer_name' => 'Jane Doe',
                    'date' => '2023-05-10',
                    'status' => 'published',
                    'response' => null
                ],
                [
                    'id' => 3,
                    'platform' => 'Facebook',
                    'rating' => 5,
                    'content' => 'Outstanding customer service and product quality!',
                    'customer_name' => 'Robert Johnson',
                    'date' => '2023-05-08',
                    'status' => 'published',
                    'response' => 'We appreciate your feedback, Robert!'
                ]
            ],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => 5,
                'total_reviews' => 15
            ]
        ]
    ];
}

// Mock review stats
function getMockReviewStats() {
    return [
        'success' => true,
        'data' => [
            'total_reviews' => 15,
            'average_rating' => 4.6,
            'rating_breakdown' => [
                '5' => 9,
                '4' => 4,
                '3' => 1,
                '2' => 1,
                '1' => 0
            ],
            'platform_breakdown' => [
                'Google' => 8,
                'Yelp' => 4,
                'Facebook' => 3
            ]
        ]
    ];
}

// Mock pending responses
function getMockPendingResponses() {
    return [
        'success' => true,
        'data' => [
            'pending_responses' => [
                [
                    'id' => 1,
                    'review_id' => 4,
                    'platform' => 'Google',
                    'rating' => 4,
                    'content' => 'Great service, would recommend!',
                    'customer_name' => 'Emily Wilson',
                    'date' => '2023-05-20'
                ],
                [
                    'id' => 2,
                    'review_id' => 5,
                    'platform' => 'Yelp',
                    'rating' => 3,
                    'content' => 'Decent service but room for improvement.',
                    'customer_name' => 'Mike Thompson',
                    'date' => '2023-05-18'
                ]
            ],
            'total_pending' => 2
        ]
    ];
}
?>
