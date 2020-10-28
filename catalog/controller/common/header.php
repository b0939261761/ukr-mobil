<?php
use Ego\Controllers\BaseController;
use Ego\Services\PrivatService;

class ControllerCommonHeader extends BaseController {

	public function index() {
		$server = $this->config->get('config_' . ($this->request->server['HTTPS'] ? 'ssl' : 'url'));

    // file_put_contents('./catalog/controller/common/__LOG__.json', json_encode($this->request));

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink("{$server}image/{$this->config->get('config_icon')}", 'icon');
		}

		$uri = substr(explode('?', $this->request->server['REQUEST_URI'], 2)[0], 1);
		$this->document->addLink("{$server}{$uri}", 'canonical');

		$data['title'] = $this->document->getTitle();
		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['metaList'] = $this->document->getMetaList();
		$data['microdata'] = $this->document->getMicrodata();
		$data['dataLayer'] = $this->document->getDataLayer();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['name'] = $this->config->get('config_name');
		$data['mytemplate'] = $this->config->get('theme_default_directory');
		$data['logo'] = "{$server}/image/{$this->config->get('config_logo')}";

		$this->load->language('common/header');

		//  Current user
		$data['customerName'] = '';
    $data['balance'] = '';
    $data['logged'] = $this->customer->isLogged();

		if ($data['logged']) {
			$data['customerName'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
      $sql = "SELECT balance FROM oc_customer WHERE customer_id = {$this->customer->getId()}";
			$data['balance'] = $this->db->query($sql)->rows[0]['balance'];
		}

    $data['home'] = $this->url->link('common/home');
		$data['login'] = $this->url->link('account/login');
		$data['logout'] = $this->url->link('account/logout');

		$data['account_documents'] = "{$this->url->link('account/account')}#documents";
		$data['account_orders'] = "{$this->url->link('account/account')}#orders";
		$data['account_profile'] = "{$this->url->link('account/account')}#profile";
		$data['account_terms'] = "{$this->url->link('account/account')}#terms";

		// $data['language'] = $this->load->controller('common/language');
		// $data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['header_menu'] = $this->load->controller('common/header_menu');
		$data['header_category'] = $this->load->controller('common/header_category');

		$type = "module";
		$this->load->model('setting/module');
		$result = $this->model_setting_module->getModule($type);
		foreach ($result as $result) {
			if ($result['code'] === "blogger") {
				$data['blog_enable'] = 1;
			}
		}

		$currencyModel = new \Ego\Models\Currency();

		$usd = $currencyModel->get('UAH', true);
		$usd = empty($usd) ? '' : $usd->getValue();

		$data['currencyData'] = [
			'signLeft' => $this->currency->getSymbolLeft($this->session->data['currency']),
			'signRight' => $this->currency->getSymbolRight($this->session->data['currency']),
			'usd' => $usd
		];

		return $this->load->view('common/header', $data);
	}
}
