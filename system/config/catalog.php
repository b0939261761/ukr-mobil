<?php
// // Site
// $_['site_url']           = HTTP_SERVER;
// $_['site_ssl']           = HTTPS_SERVER;

// Url
// $_['url_autostart']      = false;



// Session
// $_['session_autostart']  = true;
// $_['session_engine']     = 'db';
// $_['session_name']       = 'OCSESSID';

// Template
// $_['template_engine']    = 'twig';
$_['template_directory'] = 'default/template/';
$_['template_cache']     = false;

// // Autoload Libraries
// $_['library_autoload']   = array(
// 	'openbay'
// );

// Actions
$_['action_pre_action']  = [
	// 'startup/session',
	'startup/startup',
	'startup/error',
	'startup/event',
	// 'startup/maintenance',
	'startup/seo_pro'
];

$_['action_default']       = 'home/home';

// Action Events
// $_['action_event'] = array(
// 	'controller/*/before' => array(
// 		'event/language/before'
// 	),
// 	'controller/*/after' => array(
// 		'event/language/after'
// 	),
// 	'view/*/before' => array(
// 		500  => 'event/theme/override',
// 		998  => 'event/language',
// 		1000 => 'event/theme'
// 	),
// 	'language/*/after' => array(
// 		'event/translation'
// 	),
// 	//'view/*/before' => array(
// 	//	1000  => 'event/debug/before'
// 	//),
// 	'controller/*/after'  => array(
// 		'event/debug/after'
// 	)
// );
