<?php

/**
 * @category   OpenCart
 * @package    SEO Tags Generator
 * @copyright  © Serge Tkach, 2017, http://sergetkach.com/
 */

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (version_compare(PHP_VERSION, '7.2') >= 0) {
	$php_v = '72_73';
} elseif (version_compare(PHP_VERSION, '7.1') >= 0) {
	$php_v = '71';
} elseif (version_compare(PHP_VERSION, '5.6.0') >= 0) {
	$php_v = '56_70';
} elseif (version_compare(PHP_VERSION, '5.4.0') >= 0) {
	$php_v = '54_56';
} else {
	echo "Sorry! Version for PHP 5.3 Not Supported!<br>Please contact to author!";
	exit;
}

require_once DIR_SYSTEM . 'library/seo_tags_generator/seo_tags_generator_' . $php_v . '.php';
require_once DIR_SYSTEM . 'library/seo_tags_generator/admin/controller/seo_tags_generator_' . $php_v . '.php';
