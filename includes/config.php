<?php
// File: includes/config.php
// Docker specific configuration
define('DB_HOST', getenv('DB_HOST') ?: 'db');  // 'db' ist der Service-Name in Docker
define('DB_NAME', getenv('DB_NAME') ?: 'sudoku_game');
define('DB_USER', getenv('DB_USER') ?: 'sudoku_user');
define('DB_PASS', getenv('DB_PASS') ?: 'sudoku_password');

// Session configuration
define('SESSION_LIFETIME', 3600);
define('CSRF_TOKEN_NAME', 'csrf_token');

// Game configuration
define('MAX_LIVES', 3);
define('POINTS_PER_CELL', 10);
define('POINT_DEDUCTION_PER_ERROR', 5);

// Error reporting (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Timezone
date_default_timezone_set('Europe/Berlin');

// Debug mode
define('DEBUG_MODE', true);
?>