<?php
// /dheergayu/public/api/submit-contact.php
header('Content-Type: application/json');

// Allow from any origin
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($message)) {
        throw new Exception('All fields are required');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Validate phone (Sri Lankan format: 0XXXXXXXXX)
    if (!preg_match('/^0[0-9]{9}$/', $phone)) {
        throw new Exception('Invalid phone number format');
    }

    // Validate subject
    $validSubjects = ['appointment', 'treatment', 'general', 'feedback', 'other'];
    if (!in_array($subject, $validSubjects)) {
        throw new Exception('Invalid subject');
    }

    // Sanitize inputs
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO contact_submissions (name, email, phone, subject, message, status) 
        VALUES (?, ?, ?, ?, ?, 'new')
    ");
    
    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save contact submission');
    }

    $submissionId = $stmt->insert_id;
    $stmt->close();

    // Optional: Send notification email to admin
    // mail('admin@dheergayu.com', 'New Contact Form Submission', "New message from $name...");

    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent successfully!',
        'submission_id' => $submissionId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>