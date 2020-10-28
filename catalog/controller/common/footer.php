<?php

class ControllerCommonFooter extends Controller {

	public function index() {
		// $this->load->model('catalog/information');

		// $data['informations'] = array();

		// foreach ($this->model_catalog_information->getInformations() as $result) {
		// 	if ($result['bottom']) {
		// 		$data['informations'][] = array(
		// 			'title' => $result['title'],
		// 			'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
		// 		);
		// 	}
		// }

		// if ($this->request->server['HTTPS']) {
		// 	$server = $this->config->get('config_ssl');
		// } else {
		// 	$server = $this->config->get('config_url');
		// }

		//region Prepare Data
		//@todo: Replace with file path
		// $data['download_price_list'] = '#';
		// $data['news'] = $this->url->link('information/news');
		// $data['delivery'] = $this->url->link('information/delivery');
		// $data['warranty'] = $this->url->link('information/warranty');
		// $data['contact'] = $this->url->link('information/contact');
		//endregion
		// $data['contact'] = $this->url->link('information/contact');
		// $data['return'] = $this->url->link('account/return/add', '', true);
		// $data['sitemap'] = $this->url->link('information/sitemap');
		// $data['tracking'] = $this->url->link('information/tracking');
		// $data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		// if ($this->config->get('config_customer_online')) {
		// 	$this->load->model('tool/online');

		// 	if (isset($this->request->server['REMOTE_ADDR'])) {
		// 		$ip = $this->request->server['REMOTE_ADDR'];
		// 	} else {
		// 		$ip = '';
		// 	}

		// 	if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
		// 		$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
		// 	} else {
		// 		$url = '';
		// 	}

		// 	if (isset($this->request->server['HTTP_REFERER'])) {
		// 		$referer = $this->request->server['HTTP_REFERER'];
		// 	} else {
		// 		$referer = '';
		// 	}

		// 	$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		// }

		// $data['scripts'] = $this->document->getScripts('footer');
		// $data['lang_code'] = $this->language->get('code');

		
		//  !WARNING! Use constant ID
		// $data['informationPaymentInfo'] = $this->model_catalog_information->getInformation(7);
		// $data['informationPaymentInfo']['description'] = htmlspecialchars_decode($data['informationPaymentInfo']['description']);
		
		$data['home'] = $this->url->link('common/home');
		$data['ego_newsletter'] = $this->load->controller('extension/module/ego_newsletter', '', true);
		$data['is_logged'] = $this->customer->isLogged() ? 'true' : 'false';
		$data['logo'] = "/image/{$this->config->get('config_logo')}";
		$data['mytemplate'] = $this->config->get('theme_default_directory');
		return $this->load->view('common/footer', $data);
	}

}
