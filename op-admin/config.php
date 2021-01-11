<?
$domainName = 'ukrmob.reality.sh';
$uri = 'op-admin/';
$publicHtml = "/home/admin/web/{$domainName}/public_html/";

define('HTTP_CATALOG', "http://{$domainName}/");
define('HTTPS_CATALOG', "https://{$domainName}/");

define('HTTP_SERVER', HTTP_CATALOG . $uri);
define('HTTPS_SERVER', HTTPS_CATALOG . $uri);

define('DIR_APPLICATION', "{$publicHtml}{$uri}");
define('DIR_SYSTEM', "{$publicHtml}system/");
define('DIR_IMAGE', "{$publicHtml}image/");
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_CATALOG', "{$publicHtml}catalog/");
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'admin_ukrmob_tst');
define('DB_PASSWORD', 'SlSSXyN8kv');
define('DB_DATABASE', 'admin_ukrmob_tst');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

define('OPENCART_SERVER', 'https://www.opencart.com/');
