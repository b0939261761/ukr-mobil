<?php
class ControllerInformationScTracking extends Controller {
	private $error = array();

	public function index() {
		$this->document->setTitle('Проверка статуса заказа');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['mytemplate'] = $this->config->get('theme_default_directory');

		$data['is_logged'] = $this->customer->isLogged() ? true : false;

		$this->response->setOutput($this->load->view('information/sc_tracking', $data));
	}

	public function getStatus() {
		$order_id = $this->request->post['orderId'];
		$phone =  $this->request->post['phone'];

		$sql = "SELECT `name`, `price`, `state`, MAX(`datetime`) AS `datetime` "
			. "FROM sc_order_state_list "
			. "WHERE `id` = '" . $order_id . "' AND `phone` = '" . $phone . "' "
			. "GROUP BY `state` "
			. "ORDER BY `datetime`";

		$list = $this->db->query($sql)->rows;

		$this->response->setOutput(json_encode($list));
	}
}
