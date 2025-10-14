<?php
// core/bootloader.php – basic bootstrap

// Sessions
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Errors (dev-friendly)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Base config
require_once __DIR__ . '/../config/config.php';

// Simple PSR-4–like autoloader for Core\ and App\ namespaces
spl_autoload_register(function (string $class): void {
	$baseDir = dirname(__DIR__);
	$path = str_replace(['\\', 'App', 'Core'], ['/', 'app', 'core'], $class) . '.php';
	$file = $baseDir . '/' . $path;
	if (file_exists($file)) {
		require_once $file;
	}
});


