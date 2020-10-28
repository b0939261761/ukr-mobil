<?php
class ControllerAccountLogout extends Controller {
	public function index() {
		if ($this->customer->isLogged()) {
			$this->customer->logout();
			$this->response->redirect($this->url->link('account/logout', '', true));
		}

		$this->load->language('account/logout');
		$this->document->setTitle($this->language->get('heading_title'));

		$data['continue'] = $this->url->link('common/home');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('common/success', $data));
	}
}
