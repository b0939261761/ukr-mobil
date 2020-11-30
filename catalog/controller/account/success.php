<?
class ControllerAccountSuccess extends Controller {
  public function index() {
    $data['message'] = "<p>Поздравляем! Ваш Личный Кабинет был успешно создан.</p>
      <p>Теперь Вы можете воспользоваться дополнительными возможностями:
      просмотр истории заказов, печать счета, изменение своей контактной информации
      и адресов доставки и многое другое.</p>";

    $sessionId = $this->db->escape($this->session->getId());
    $customerId = (int)$this->customer->getId();
    $sql = "
      SELECT COALESCE(SUM(quantity), 0) AS quantity FROM oc_cart
      WHERE session_id = '{$sessionId}' AND customer_id = {$customerId}";

    $data['linkContinue'] = $this->db->query($sql)->row['quantity']
      ? $this->url->link('checkout/cart')
      : $this->url->link('account/account');

    $data['headingH1'] = 'Регистрация';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('account/success', $data));
  }
}
