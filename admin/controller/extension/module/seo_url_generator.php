<?php

/**
 * @category   OpenCart
 * @package    SEO URL Generator PRO
 * @copyright  © Serge Tkach, 2018, http://sergetkach.com/
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$file = DIR_SYSTEM . 'library/seo_url_generator/seo_url_generator.php';

if (is_file($file)) {
	include $file;
} else {
	echo "No file '$file'<br>";
	exit;
}
