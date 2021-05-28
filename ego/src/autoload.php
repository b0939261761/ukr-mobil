<?php

// require_once __DIR__ . '/bootstrap/utils.php';
// require_once __DIR__ . '/bootstrap/main.php';

spl_autoload_register(function ($class) {
	$class = ltrim($class, '\\');

	if (strpos($class, 'Ego\\') !== 0) {
		return;
	}

	$classPath = substr($class, strlen('Ego'), strlen($class));

	$classPath = __DIR__ . str_replace('\\', '/', $classPath) . '.php';

	require_once $classPath;
});
