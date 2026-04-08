<?php
// /dheergayu/public/api/reply-contact.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $id = intval($_POST['id'] ?? 0);
    $replyMessage = trim($_POST['reply_message'] ?? '');
    $recipientEmail = trim($_POST['recipient_email'] ?? '');
    $recipientName = trim($_POST['recipient_name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');

    if ($id <= 0) {
        throw new Exception('Invalid submission ID');
    }

    if (empty($replyMessage)) {
        throw new Exception('Reply message is required');
    }

    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid recipient email');
    }

    // Ensure admin_reply and replied_at columns exist
    $checkCol = $conn->query("SHOW COLUMNS FROM contact_submissions LIKE 'admin_reply'");
    if ($checkCol->num_rows === 0) {
        $conn->query("ALTER TABLE contact_submissions ADD COLUMN admin_reply TEXT DEFAULT NULL AFTER message");
        $conn->query("ALTER TABLE contact_submissions ADD COLUMN replied_at TIMESTAMP NULL DEFAULT NULL AFTER admin_reply");
    }

    // Save the reply to the database
    $stmt = $conn->prepare("UPDATE contact_submissions SET admin_reply = ?, status = 'replied', replied_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $replyMessage, $id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to save reply');
    }
    $stmt->close();

    // Send email reply
    $emailSubject = "Re: " . ucfirst($subject) . " - Dheergayu Ayurvedic Centre";
    $emailBody = "Dear " . htmlspecialchars($recipientName) . ",\r\n\r\n";
    $emailBody .= $replyMessage . "\r\n\r\n";
    $emailBody .= "Best regards,\r\n";
    $emailBody .= "Dheergayu Ayurvedic Centre\r\n";
    $emailBody .= "Email: admindheergayu@gmail.com\r\n";

    $headers = "From: Dheergayu Ayurvedic Centre <admindheergayu@gmail.com>\r\n";
    $headers .= "Reply-To: admindheergayu@gmail.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $emailSent = @mail($recipientEmail, $emailSubject, $emailBody, $headers);

    echo json_encode([
        'success' => true,
        'message' => $emailSent ? 'Reply sent successfully!' : 'Reply saved but email delivery may be delayed.',
        'email_sent' => $emailSent
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
