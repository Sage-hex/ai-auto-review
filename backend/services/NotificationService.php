<?php
/**
 * NotificationService Class
 * 
 * Handles sending notifications to users via email and in-app notifications
 */
class NotificationService {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Send an email notification
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email message (HTML)
     * @param array $attachments Optional file attachments
     * @return bool True if email was sent successfully, false otherwise
     */
    public function sendEmail($to, $subject, $message, $attachments = []) {
        try {
            // Set email headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . COMPANY_NAME . " <" . NOTIFICATION_EMAIL . ">" . "\r\n";
            
            // Add email template
            $emailContent = $this->getEmailTemplate($subject, $message);
            
            // Send email
            $result = mail($to, $subject, $emailContent, $headers);
            
            // Log email sending
            Logger::info("Email sent to $to with subject: $subject", ['result' => $result]);
            
            return $result;
        } catch (Exception $e) {
            Logger::exception($e, ['to' => $to, 'subject' => $subject]);
            return false;
        }
    }
    
    /**
     * Get HTML email template with content
     *
     * @param string $subject Email subject
     * @param string $content Email content
     * @return string Complete HTML email
     */
    private function getEmailTemplate($subject, $content) {
        $year = date('Y');
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>$subject</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #4f46e5;
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                .content {
                    padding: 20px;
                    background-color: #f9fafb;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    font-size: 12px;
                    color: #6b7280;
                }
                .button {
                    display: inline-block;
                    background-color: #4f46e5;
                    color: white;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>$subject</h1>
                </div>
                <div class="content">
                    $content
                </div>
                <div class="footer">
                    <p>&copy; $year " . COMPANY_NAME . ". All rights reserved.</p>
                    <p>This email was sent to you because you are a registered user of our service.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
    
    /**
     * Create an in-app notification for a user
     *
     * @param int $userId User ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type (info, warning, error, success)
     * @param string $link Optional link to redirect when clicked
     * @return bool True if notification was created successfully, false otherwise
     */
    public function createNotification($userId, $title, $message, $type = 'info', $link = null) {
        try {
            $query = "INSERT INTO notifications (user_id, title, message, type, link, created_at) 
                      VALUES (:user_id, :title, :message, :type, :link, NOW())";
            
            $params = [
                ':user_id' => $userId,
                ':title' => $title,
                ':message' => $message,
                ':type' => $type,
                ':link' => $link
            ];
            
            $this->db->query($query, $params);
            
            return true;
        } catch (Exception $e) {
            Logger::exception($e, [
                'user_id' => $userId, 
                'title' => $title,
                'type' => $type
            ]);
            return false;
        }
    }
    
    /**
     * Create notifications for all users in a business
     *
     * @param int $businessId Business ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type (info, warning, error, success)
     * @param string $link Optional link to redirect when clicked
     * @param array $roles Optional array of roles to limit notification to
     * @return bool True if notifications were created successfully, false otherwise
     */
    public function notifyBusiness($businessId, $title, $message, $type = 'info', $link = null, $roles = null) {
        try {
            // Get users for the business
            $query = "SELECT id FROM users WHERE business_id = :business_id";
            
            // Add role filter if specified
            if ($roles !== null && is_array($roles) && !empty($roles)) {
                $placeholders = implode(',', array_fill(0, count($roles), '?'));
                $query .= " AND role IN ($placeholders)";
            }
            
            $params = [':business_id' => $businessId];
            
            // Add role values to params if needed
            if ($roles !== null && is_array($roles) && !empty($roles)) {
                foreach ($roles as $role) {
                    $params[] = $role;
                }
            }
            
            $users = $this->db->query($query, $params);
            
            if (!$users) {
                return false;
            }
            
            // Create notification for each user
            foreach ($users as $user) {
                $this->createNotification($user['id'], $title, $message, $type, $link);
            }
            
            return true;
        } catch (Exception $e) {
            Logger::exception($e, [
                'business_id' => $businessId, 
                'title' => $title,
                'type' => $type
            ]);
            return false;
        }
    }
    
    /**
     * Mark a notification as read
     *
     * @param int $notificationId Notification ID
     * @param int $userId User ID (for security check)
     * @return bool True if notification was marked as read, false otherwise
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $query = "UPDATE notifications SET 
                      is_read = 1,
                      read_at = NOW()
                      WHERE id = :notification_id AND user_id = :user_id";
            
            $params = [
                ':notification_id' => $notificationId,
                ':user_id' => $userId
            ];
            
            $this->db->query($query, $params);
            
            return true;
        } catch (Exception $e) {
            Logger::exception($e, [
                'notification_id' => $notificationId, 
                'user_id' => $userId
            ]);
            return false;
        }
    }
    
    /**
     * Get unread notifications for a user
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of notifications to return
     * @return array Array of unread notifications
     */
    public function getUnreadNotifications($userId, $limit = 10) {
        try {
            $query = "SELECT * FROM notifications 
                      WHERE user_id = :user_id AND is_read = 0
                      ORDER BY created_at DESC
                      LIMIT :limit";
            
            $params = [
                ':user_id' => $userId,
                ':limit' => $limit
            ];
            
            return $this->db->query($query, $params) ?: [];
        } catch (Exception $e) {
            Logger::exception($e, ['user_id' => $userId]);
            return [];
        }
    }
    
    /**
     * Get all notifications for a user
     *
     * @param int $userId User ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of notifications with pagination info
     */
    public function getAllNotifications($userId, $page = 1, $perPage = 20) {
        try {
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id";
            $countParams = [':user_id' => $userId];
            $countResult = $this->db->query($countQuery, $countParams);
            $totalCount = $countResult ? (int)$countResult[0]['count'] : 0;
            
            // Get notifications for current page
            $query = "SELECT * FROM notifications 
                      WHERE user_id = :user_id
                      ORDER BY created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $params = [
                ':user_id' => $userId,
                ':limit' => $perPage,
                ':offset' => $offset
            ];
            
            $notifications = $this->db->query($query, $params) ?: [];
            
            // Calculate pagination info
            $totalPages = ceil($totalCount / $perPage);
            
            return [
                'data' => $notifications,
                'pagination' => [
                    'total' => $totalCount,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ];
        } catch (Exception $e) {
            Logger::exception($e, ['user_id' => $userId]);
            return [
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => 0
                ]
            ];
        }
    }
    
    /**
     * Send notification about a new review
     *
     * @param int $businessId Business ID
     * @param array $review Review data
     * @return bool True if notification was sent successfully, false otherwise
     */
    public function notifyNewReview($businessId, $review) {
        try {
            // Get business settings
            $query = "SELECT name, email FROM businesses WHERE id = :business_id";
            $params = [':business_id' => $businessId];
            $business = $this->db->query($query, $params);
            
            if (!$business) {
                return false;
            }
            
            $businessName = $business[0]['name'];
            $businessEmail = $business[0]['email'];
            
            // Get notification settings
            $query = "SELECT * FROM notification_settings WHERE business_id = :business_id";
            $params = [':business_id' => $businessId];
            $settings = $this->db->query($query, $params);
            
            if (!$settings || !$settings[0]['email_notifications'] || !$settings[0]['new_review_notification']) {
                return true; // Skip if notifications are disabled
            }
            
            // Check if it's a negative review and negative alerts are enabled
            $isNegative = $review['rating'] <= 3;
            if ($isNegative && !$settings[0]['negative_review_notification']) {
                return true; // Skip if negative alerts are disabled
            }
            
            // Create in-app notification for admins and managers
            $notificationType = $isNegative ? 'warning' : 'info';
            $notificationTitle = $isNegative ? 'New Negative Review' : 'New Review';
            $notificationMessage = "A new " . ($isNegative ? "negative " : "") . "review ({$review['rating']} stars) has been received on {$review['platform']}.";
            $notificationLink = "/reviews/{$review['id']}";
            
            $this->notifyBusiness(
                $businessId,
                $notificationTitle,
                $notificationMessage,
                $notificationType,
                $notificationLink,
                ['admin', 'manager']
            );
            
            // Send email notification
            $subject = $notificationTitle . " for " . $businessName;
            $message = <<<HTML
            <p>Hello,</p>
            <p>A new {$review['rating']}-star review has been received for {$businessName} on {$review['platform']}.</p>
            <p><strong>From:</strong> {$review['user_name']}</p>
            <p><strong>Rating:</strong> {$review['rating']} stars</p>
            <p><strong>Review:</strong></p>
            <blockquote style="border-left: 4px solid #e5e7eb; padding-left: 16px; margin-left: 0;">
                {$review['content']}
            </blockquote>
            <p>
                <a href="https://yourdomain.com/reviews/{$review['id']}" style="display: inline-block; background-color: #4f46e5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-top: 20px;">
                    View and Respond
                </a>
            </p>
            HTML;
            
            $this->sendEmail($businessEmail, $subject, $message);
            
            return true;
        } catch (Exception $e) {
            Logger::exception($e, [
                'business_id' => $businessId, 
                'review_id' => $review['id']
            ]);
            return false;
        }
    }
    
    /**
     * Send weekly summary email
     *
     * @param int $businessId Business ID
     * @return bool True if summary was sent successfully, false otherwise
     */
    public function sendWeeklySummary($businessId) {
        try {
            // Get business settings
            $query = "SELECT name, email FROM businesses WHERE id = :business_id";
            $params = [':business_id' => $businessId];
            $business = $this->db->query($query, $params);
            
            if (!$business) {
                return false;
            }
            
            $businessName = $business[0]['name'];
            $businessEmail = $business[0]['email'];
            
            // Get notification settings
            $query = "SELECT * FROM notification_settings WHERE business_id = :business_id";
            $params = [':business_id' => $businessId];
            $settings = $this->db->query($query, $params);
            
            if (!$settings || !$settings[0]['email_notifications'] || !$settings[0]['weekly_summary']) {
                return true; // Skip if notifications are disabled
            }
            
            // Get review stats for the past week
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
            
            $query = "SELECT 
                      COUNT(*) as total_reviews,
                      AVG(rating) as average_rating,
                      SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                      SUM(CASE WHEN rating <= 3 THEN 1 ELSE 0 END) as negative_reviews,
                      COUNT(DISTINCT platform) as platforms
                      FROM reviews 
                      WHERE business_id = :business_id
                      AND DATE(created_at) BETWEEN :start_date AND :end_date";
            
            $params = [
                ':business_id' => $businessId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ];
            
            $stats = $this->db->query($query, $params);
            
            if (!$stats) {
                return false;
            }
            
            // Get response stats
            $query = "SELECT 
                      COUNT(*) as total_responses,
                      SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_responses,
                      SUM(CASE WHEN is_ai_generated = 1 THEN 1 ELSE 0 END) as ai_responses
                      FROM responses 
                      WHERE business_id = :business_id
                      AND DATE(created_at) BETWEEN :start_date AND :end_date";
            
            $params = [
                ':business_id' => $businessId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ];
            
            $responseStats = $this->db->query($query, $params);
            
            // Format the email content
            $subject = "Weekly Review Summary for " . $businessName;
            $message = <<<HTML
            <p>Hello,</p>
            <p>Here's your weekly review summary for {$businessName} from {$startDate} to {$endDate}:</p>
            
            <h2 style="color: #4f46e5;">Review Statistics</h2>
            <ul>
                <li><strong>Total Reviews:</strong> {$stats[0]['total_reviews']}</li>
                <li><strong>Average Rating:</strong> {$stats[0]['average_rating']}</li>
                <li><strong>Positive Reviews (4-5 stars):</strong> {$stats[0]['positive_reviews']}</li>
                <li><strong>Negative Reviews (1-3 stars):</strong> {$stats[0]['negative_reviews']}</li>
            </ul>
            
            <h2 style="color: #4f46e5;">Response Statistics</h2>
            <ul>
                <li><strong>Total Responses:</strong> {$responseStats[0]['total_responses']}</li>
                <li><strong>Published Responses:</strong> {$responseStats[0]['published_responses']}</li>
                <li><strong>AI-Generated Responses:</strong> {$responseStats[0]['ai_responses']}</li>
            </ul>
            
            <p>
                <a href="https://yourdomain.com/dashboard" style="display: inline-block; background-color: #4f46e5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-top: 20px;">
                    View Full Dashboard
                </a>
            </p>
            HTML;
            
            $this->sendEmail($businessEmail, $subject, $message);
            
            return true;
        } catch (Exception $e) {
            Logger::exception($e, ['business_id' => $businessId]);
            return false;
        }
    }
}
