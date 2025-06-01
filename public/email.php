<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

header('Content-Type: application/json');

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

// Get POST request data
$data = json_decode(file_get_contents("php://input"), true);

$name = htmlspecialchars(trim($data['name'] ?? ''));
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(trim($data['message'] ?? ''));

// Validate input
if (!$email) {
    echo json_encode(["error" => "Invalid email address."]);
    exit;
}

if (empty($name) || empty($message)) {
    echo json_encode(["error" => "Name and message are required."]);
    exit;
}

if (!empty($data['honeypot'])) {
    echo json_encode(["error" => "Spam detected."]);
    exit;
}

// Set up PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'jmillerdevelops@gmail.com';  
    $mail->Password   = $_ENV['EMAIL_APP_PASSWORD'];  
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('jmillerdevelops@gmail.com', 'Contact Form');
    $mail->addAddress('jmillerdevelops@gmail.com');
    $mail->addReplyTo($email, $name); // Prevents spoofing issues

    $mail->Subject = 'New Contact Form Submission';
    $mail->Body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    $mail->send();

    echo json_encode(["success" => "Message sent successfully!"]);
    exit;
} catch (Exception $e) {
    echo json_encode(["error" => "Message could not be sent. Error: {$mail->ErrorInfo}"]);
    exit;
}
?>
