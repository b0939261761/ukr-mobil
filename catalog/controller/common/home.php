<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		// if (isset($this->request->get['route'])) {
		// 	$this->document->addLink($this->config->get('config_url'), 'canonical');
		// }

		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');
		$data['module_product_new'] = $this->load->controller('extension/module/ego_product_new');
		$data['module_product_stocks'] = $this->load->controller('extension/module/ego_product_stocks');
		$data['module_news'] = $this->load->controller('extension/module/ego_news');

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
