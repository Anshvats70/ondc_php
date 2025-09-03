<?php
echo "PHP Version: " . phpversion() . "\n";
echo "cURL Extension: " . (extension_loaded('curl') ? 'Loaded' : 'Not Loaded') . "\n";

if (extension_loaded('curl')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/get');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Test: " . ($httpCode == 200 ? 'Success' : 'Failed (HTTP ' . $httpCode . ')') . "\n";
} else {
    echo "cURL extension not available\n";
}
?>
