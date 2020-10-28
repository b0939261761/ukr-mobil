<?php
class ControllerAccountSuccess extends Controller {
	public function index() {
    $this->document->setTitle('Ваша учетная запись создана!');

    $data['text_message'] = "<p>Поздравляем! Ваш Личный Кабинет был успешно создан.</p>
      <p>Теперь Вы можете воспользоваться дополнительными возможностями:
      просмотр истории заказов, печать счета, изменение своей контактной информации
      и адресов доставки и многое другое.</p>";

    $data['continue'] = $this->cart->hasProducts()
      ? $this->url->link('checkout/cart')
	    : $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}
