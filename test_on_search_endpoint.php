<?php
/**
 * Test endpoint to simulate buyer's /on_search callback
 * This simulates what the buyer would receive when we send the on_search callback
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the incoming callback data
$input = file_get_contents('php://input');
$callbackData = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Log the received callback
$logFile = 'logs/on_search_callback.log';
$logsDir = dirname($logFile);
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

$timestamp = date('Y-m-d H:i:s');
$logMessage = "[$timestamp] 📥 Received on_search callback\n";
$logMessage .= "Headers: " . json_encode(getallheaders()) . "\n";
$logMessage .= "Data: " . json_encode($callbackData, JSON_PRETTY_PRINT) . "\n";
$logMessage .= str_repeat("-", 80) . "\n";

file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);

// Validate the callback
$validationResult = validateCallback($callbackData);

if ($validationResult['valid']) {
    echo json_encode([
        'status' => 'success',
        'message' => 'on_search callback received successfully',
        'received_data' => $callbackData,
        'validation' => $validationResult
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid callback data',
        'validation_errors' => $validationResult['errors']
    ], JSON_PRETTY_PRINT);
}

/**
 * Validate the incoming callback
 */
function validateCallback($data) {
    $errors = [];
    
    // Check required fields
    if (!isset($data['context'])) {
        $errors[] = 'Missing context';
    } else {
        $context = $data['context'];
        $requiredContextFields = ['domain', 'action', 'bap_id', 'bap_uri', 'bpp_id', 'bpp_uri', 'transaction_id', 'message_id'];
        
        foreach ($requiredContextFields as $field) {
            if (!isset($context[$field])) {
                $errors[] = "Missing context field: $field";
            }
        }
        
        if (isset($context['action']) && $context['action'] !== 'on_search') {
            $errors[] = 'Invalid action: expected "on_search"';
        }
    }
    
    if (!isset($data['message'])) {
        $errors[] = 'Missing message';
    } else {
        $message = $data['message'];
        if (!isset($message['ack']) || !isset($message['catalog'])) {
            $errors[] = 'Missing required message fields: ack, catalog';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>
