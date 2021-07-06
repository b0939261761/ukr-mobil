<?php
// Site
// $_['site_url']          = HTTP_SERVER;
// $_['site_ssl']          = HTTPS_SERVER;

// Session
// $_['session_autostart'] = true;

// Template
$_['template_cache']     = true;
$_['template_directory'] = '';

// Actions
$_['action_pre_action'] = [
	'startup/startup',
	'startup/error',
	'startup/event',
	'startup/sass',
	'startup/login',
	'startup/permission'
];

// Actions
$_['action_default'] = 'common/dashboard';

// Action Events
$_['action_event'] = array(
	'controller/*/before' => array(
		'event/language/before'
	),
	'controller/*/after' => array(
		'event/language/after'
	),
	'view/*/before' => array(
		999  => 'event/language',
		1000 => 'event/theme'
	),
	'view/*/before' => array(
		'event/language'
	)
);
