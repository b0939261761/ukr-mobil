<?php

use Philo\Blade\Blade;

/**
 * Return view
 *
 * @param string $view
 * @param array $data
 * @return mixed
 */
function _view(string $view, $data = []) {
	$blade = new Blade(__DIR__ . '/../../views', __DIR__ . '/../../views/cache');

	return $blade->view()->make($view, $data)->render();
}

/**
 * Return environment variable
 *
 * @param $key
 * @param null $default
 * @return null
 */
function _env($key, $default = null) {
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$envPath = $rootPath . '/.env';

	if (!file_exists($envPath)) {
		return null;
	}

	$content = file_get_contents($envPath);

	foreach (explode("\n", $content) as $row) {
		$tmp = explode('=', $row);
		$itemKey = trim($tmp[0]);
		$itemValue = array_key_exists(1, $tmp) ? $tmp[1] : null;

		if ($key === $itemKey) {
			return $itemValue;
		}
	}

	return $default;
}
