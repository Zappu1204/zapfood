<?php
/**
 * Configuration File
 * Loads environment variables and provides centralized configuration
 */

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Validate required environment variables
$dotenv->required([
    'DB_HOST', 
    'DB_NAME', 
    'DB_USER', 
    'DB_PASS',
    'SMTP_HOST',
    'SMTP_USERNAME',
    'SMTP_PASSWORD',
    'RECAPTCHA_SITE_KEY',
    'RECAPTCHA_SECRET_KEY'
]);

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);

// SMTP Configuration
define('SMTP_HOST', $_ENV['SMTP_HOST']);
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME']);
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD']);
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL']);
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME']);
define('SMTP_TO_EMAIL', $_ENV['SMTP_TO_EMAIL']);
define('SMTP_TO_NAME', $_ENV['SMTP_TO_NAME']);

// reCAPTCHA Configuration
define('RECAPTCHA_SITE_KEY', $_ENV['RECAPTCHA_SITE_KEY']);
define('RECAPTCHA_SECRET_KEY', $_ENV['RECAPTCHA_SECRET_KEY']);
define('RECAPTCHA_MIN_SCORE', $_ENV['RECAPTCHA_MIN_SCORE'] ?? 0.5);

// Application Configuration
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

/**
 * Get PDO database connection
 * @return PDO
 * @throws PDOException
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", 
                DB_USER, 
                DB_PASS, 
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed");
        }
    }
    
    return $pdo;
}

/**
 * Verify reCAPTCHA v3 token
 * @param string $token The reCAPTCHA token from the form
 * @param string $action The expected action name
 * @return array ['success' => bool, 'score' => float, 'message' => string]
 */
function verifyRecaptcha($token, $action = 'submit') {
    if (empty($token)) {
        return [
            'success' => false,
            'score' => 0,
            'message' => 'reCAPTCHA token is missing'
        ];
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        error_log("reCAPTCHA verification request failed");
        return [
            'success' => false,
            'score' => 0,
            'message' => 'reCAPTCHA verification request failed'
        ];
    }
    
    $responseData = json_decode($result, true);
    
    if (!isset($responseData['success'])) {
        return [
            'success' => false,
            'score' => 0,
            'message' => 'Invalid reCAPTCHA response'
        ];
    }
    
    $success = $responseData['success'] === true;
    $score = $responseData['score'] ?? 0;
    $responseAction = $responseData['action'] ?? '';
    
    // Verify the action matches
    if ($success && $responseAction !== $action) {
        error_log("reCAPTCHA action mismatch. Expected: $action, Got: $responseAction");
        return [
            'success' => false,
            'score' => $score,
            'message' => 'reCAPTCHA action mismatch'
        ];
    }
    
    // Check if score meets minimum threshold
    if ($success && $score < RECAPTCHA_MIN_SCORE) {
        return [
            'success' => false,
            'score' => $score,
            'message' => 'reCAPTCHA score too low'
        ];
    }
    
    return [
        'success' => $success,
        'score' => $score,
        'message' => $success ? 'reCAPTCHA verification successful' : ($responseData['error-codes'][0] ?? 'reCAPTCHA verification failed')
    ];
}

/**
 * Send error response and exit
 * @param string $message Error message
 * @param int $httpCode HTTP status code
 */
function sendError($message, $httpCode = 400) {
    http_response_code($httpCode);
    
    if (APP_DEBUG) {
        echo $message;
    } else {
        echo "An error occurred. Please try again later.";
    }
    
    error_log($message);
    exit;
}

/**
 * Send success response and exit
 * @param string $message Success message
 */
function sendSuccess($message = 'OK') {
    http_response_code(200);
    echo $message;
    exit;
}
