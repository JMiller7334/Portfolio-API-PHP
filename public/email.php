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

//RATE LIMITING

//temp dir clean up
$files = glob(sys_get_temp_dir() . '/rate_limit_*');
$expiry = 3600; // 1 hour
$now = time();

foreach ($files as $file) {
    if (filemtime($file) < $now - $expiry) {
        @unlink($file);
    }
}

//rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
if (empty($ip)) {
    http_response_code(400);
    echo json_encode(["error" => "Unable to determine client IP."]);
    exit;
}

$rateLimitFile = sys_get_temp_dir() . "/rate_limit_" . md5($ip);
$limit = 3;   //max requests allowed
$window = 60; //time window in seconds

$fp = fopen($rateLimitFile, 'c+'); //open or create file
if (!$fp) {
    http_response_code(500);
    echo json_encode(["error" => "Internal server error, fopen failure."]);
    exit;
}

if (flock($fp, LOCK_EX)) {///ock file for exclusive access
    rewind($fp);
    $contents = stream_get_contents($fp);
    $requests = json_decode($contents, true) ?? [];

    //remove timestamps outside the current window
    $requests = array_filter($requests, fn($timestamp) => $timestamp > $now - $window);

    if (count($requests) >= $limit) {
        flock($fp, LOCK_UN);
        fclose($fp);
        http_response_code(429);
        echo json_encode(["error" => "Too many requests. Please wait and try again later."]);
        exit;
    }

    //add current request timestamp
    $requests[] = $now;

    //save updated request list
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($requests));
    fflush($fp);
    flock($fp, LOCK_UN);
}
fclose($fp);
//

// Get POST request data
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) { //validate json
    echo json_encode(["error" => "Invalid JSON payload."]);
    exit;
}


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
