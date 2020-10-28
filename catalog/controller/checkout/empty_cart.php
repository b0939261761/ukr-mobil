<?php
use Ego\Controllers\BaseController;

class ControllerCheckoutEmptyCart extends BaseController {
  public function index() {
    $this->document->setTitle('Пустая корзина');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('checkout/empty_cart', $data));
  }
}
