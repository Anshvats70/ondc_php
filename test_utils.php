<?php
/**
 * Test Utilities for ONDC Project
 * Provides dynamic base URL and common test functions
 */

// Load environment variables
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeload();
}

/**
 * Get the base URL from environment or use default
 */
function getBaseUrl() {
    return $_ENV['BASE_URL'] ?? 'http://localhost';
}

/**
 * Get the full project URL
 */
function getProjectUrl($path = '') {
    $baseUrl = getBaseUrl();
    $projectPath = '/ondc';
    
    if ($path) {
        $projectPath .= '/' . ltrim($path, '/');
    }
    
    return $baseUrl . $projectPath;
}

/**
 * Get the search endpoint URL
 */
function getSearchUrl() {
    return getProjectUrl('src/search.php');
}

/**
 * Get the on_search endpoint URL
 */
function getOnSearchUrl() {
    return getProjectUrl('src/on_search.php');
}

/**
 * Get the test callback endpoint URL
 */
function getTestCallbackUrl() {
    return getProjectUrl('test_on_search_endpoint.php');
}

/**
 * Display environment information
 */
function displayEnvironmentInfo() {
    echo "🔧 Environment Configuration:\n";
    echo "   Base URL: " . getBaseUrl() . "\n";
    echo "   Project URL: " . getProjectUrl() . "\n";
    echo "   Search Endpoint: " . getSearchUrl() . "\n";
    echo "   On Search Endpoint: " . getOnSearchUrl() . "\n";
    echo "   Test Callback: " . getTestCallbackUrl() . "\n\n";
}
?>
