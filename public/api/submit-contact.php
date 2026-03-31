<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

function getInput(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function requireFields(array $data, array $requiredKeys): void
{
    foreach ($requiredKeys as $key) {
        if (!isset($data[$key]) || $data[$key] === '') {
            throw new Exception('All fields are required');
        }
    }
}

function validateContactData(array $data): void
{
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    if (!preg_match('/^0[0-9]{9}$/', $data['phone'])) {
        throw new Exception('Invalid phone number format');
    }

    $validSubjects = ['appointment', 'treatment', 'general', 'feedback', 'other'];
    if (!in_array($data['subject'], $validSubjects, true)) {
        throw new Exception('Invalid subject');
    }
}

function sanitizeContactData(array $data): array
{
    return [
        'name' => htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'),
        'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
        'phone' => preg_replace('/[^0-9]/', '', $data['phone']),
        'subject' => htmlspecialchars($data['subject'], ENT_QUOTES, 'UTF-8'),
        'message' => htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'),
    ];
}

try {
    $contactData = [
        'name' => getInput('name'),
        'email' => getInput('email'),
        'phone' => preg_replace('/[^0-9]/', '', getInput('phone')),
        'subject' => getInput('subject'),
        'message' => getInput('message'),
    ];

    requireFields($contactData, ['name', 'email', 'phone', 'subject', 'message']);
    validateContactData($contactData);
    $clean = sanitizeContactData($contactData);

    $stmt = $conn->prepare("
        INSERT INTO contact_submissions (name, email, phone, subject, message, status) 
        VALUES (?, ?, ?, ?, ?, 'new')
    ");

    $stmt->bind_param("sssss", $clean['name'], $clean['email'], $clean['phone'], $clean['subject'], $clean['message']);

    if (!$stmt->execute()) {
        throw new Exception('Failed to save contact submission');
    }

    $submissionId = $stmt->insert_id;
    $stmt->close();

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