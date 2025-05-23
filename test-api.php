<?php
// Simple test script to directly test the auth handler
$url = 'http://localhost/AiAutoReview/backend/api/endpoints/auth/handler.php';
$data = [
    'business_name' => 'Test Business',
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Response:\n";
echo $result;
?>
