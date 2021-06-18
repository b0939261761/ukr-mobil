<?php
// class ControllerEventTheme extends Controller {
// 	public function index(&$route, &$args, &$template) {


// 		// if ($template) {
// 		// 	// include and register Twig auto-loader
// 		// 	include_once(DIR_SYSTEM . 'library/template/Twig/Autoloader.php');

// 		// 	Twig_Autoloader::register();

// 		// 	// specify where to look for templates
// 		// 	$loader = new \Twig_Loader_Filesystem(DIR_TEMPLATE);

// 		// 	$config = array('autoescape' => false);

// 		// 	if ($this->config->get('template_cache')) {
// 		// 		$config['cache'] = DIR_CACHE;
// 		// 	}

// 		// 	// initialize Twig environment
// 		// 	$twig = new \Twig_Environment($loader, $config);

// 		// 	return $twig->createTemplate($template)->render($args);
// 		// }
// 	}

// 	// public function override(&$route, &$args, &$template) {
// 	// 	// // If the default theme is selected we need to know which directory its pointing to
// 	// 	// if ($this->config->get('config_theme') == 'default') {
// 	// 	// 	$theme = $this->config->get('theme_default_directory');
// 	// 	// } else {
// 	// 	// 	$theme = $this->config->get('config_theme');
// 	// 	// }

// 	// 	// If there is a theme override we should get it


//   //   // $query = $this->db->query("SELECT * FROM oc_theme WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' AND theme = '" . $this->db->escape($theme) . "' AND route = '" . $this->db->escape($route) . "'");

// 	// 	// $this->load->model('design/theme');

// 	// 	// $theme_info = $this->model_design_theme->getTheme($route, 'default');

// 	// 	// if ($theme_info) {
// 	// 	// 	$template = html_entity_decode($theme_info['code'], ENT_QUOTES, 'UTF-8');
// 	// 	// } elseif (is_file(DIR_TEMPLATE . $theme . '/template/' . $route . '.twig')) {
// 	// 	// 	$this->config->set('template_directory', $theme . '/template/');
// 	// 	// } elseif (is_file(DIR_TEMPLATE . 'default/template/' . $route . '.twig')) {
// 	// 	// 	$this->config->set('template_directory', 'default/template/');
// 	// 	// }

//   //   $this->config->set('template_directory', 'default/template/');
// 	// }
// }
