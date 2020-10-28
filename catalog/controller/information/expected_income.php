<?php
class ControllerInformationExpectedIncome extends Controller {
	private $error = array();

	public function index() {
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['mytemplate'] = $this->config->get('theme_default_directory');

		$data['is_logged'] = $this->customer->isLogged() ? true : false;
		$data['is_newsletter'] = empty($this->customer->getNewsLetter()) ? false : true;

		$this->load->language('information/expected_income');
		$data['documents'] = $this->documents();

		$this->document->setTitle($this->language->get('heading_title'));
		$this->response->setOutput($this->load->view('information/income', $data));
	}

	private function documents() {
		$this->load->model('tool/image');
		$customerGroupId = $this->customer->getGroupId() ?? 0;

		$sql_documents = "
      SELECT income_number, date_expected_income,
				DATE_FORMAT(date_expected_income, '%d.%m.%Y') AS date
			FROM oc_product_expected_income
			GROUP BY date_expected_income, income_number
      ORDER BY date_expected_income DESC, income_number DESC LIMIT 10
    ";
		$documents = $this->db->query($sql_documents)->rows;

		foreach ($documents as $index=>$value) {
			$sql = "
        SELECT aa.product_id, bb.name, COALESCE(dd.price, cc.price) AS price,
					 aa.quantity, cc.image
				FROM oc_product_expected_income aa
				INNER JOIN oc_product_description bb ON bb.product_id = aa.product_id
				INNER JOIN oc_product cc ON cc.product_id = aa.product_id
				LEFT JOIN oc_product_discount dd ON dd.product_id = aa.product_id
					AND dd.customer_group_id = {$customerGroupId}
				WHERE aa.income_number = '{$value['income_number']}'
          AND aa.date_expected_income = '{$value['date_expected_income']}'
          AND bb.language_id = 2
        ORDER BY bb.name
      ";

			foreach ($this->db->query($sql)->rows as $product) {
				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				$documents[$index]['products'][] = array(
					'image'       => $image,
					'code'        => $this->language->get('text_code') . ": " . $product['product_id'],
					'name'        => $product['name'],
					'price'       => "$" . round($product['price'], 2),
					'quantity'    => $product['quantity'] . " шт.",
					'href'        => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}
		}
		return $documents;
	}

	// Оформляем подписку
	public function newsletter() {
		$customer_id = $this->customer->getId();
		$this->db->query("UPDATE oc_customer SET newsletter = 1 WHERE customer_id = " . $customer_id);
		$this->response->setOutput('true');
	}
}
