<?
class ControllerAccountReset extends Controller {
  private $errorPassword = '';
  private $errorConfirm = '';

  public function index() {
    if ($this->customer->isLogged()) $this->response->redirect($this->url->link('account/account'));

    $code = $this->db->escape($this->request->get['code'] ?? '');
    $sql = "SELECT customer_id AS id, email FROM oc_customer WHERE code = '{$code}'";
    $customer = $this->db->query($sql)->row;

    if (empty($customer)) {
      $message = 'Код восстановления пароля ошибочный!';
      $redirect = $this->url->link('account/login', ['error' => $message]);
      $this->response->redirect($redirect);
    }

    $data['password'] = trim($this->request->post['password'] ?? '');
    $data['confirm'] = trim($this->request->post['confirm'] ?? '');

    if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
      $salt = token(9);
      $sql = "
        UPDATE oc_customer SET
          password = SHA1(CONCAT('{$salt}', SHA1(CONCAT('{$salt}', SHA1('{$data['password']}'))))),
          salt     = '{$salt}',
          code     = ''
        WHERE customer_id = {$customer['id']}
      ";
      $this->db->query($sql);
      $this->customer->login($customer['email'], $data['password']);
      $this->response->redirect($this->url->link('account/account'));
    }

    $data['action'] = $this->url->link('account/reset', ['code' => $code]);
    $data['errorPassword'] = $this->errorPassword;
    $data['errorConfirm'] = $this->errorConfirm;
    $data['linkLogin'] = $this->url->link('account/login');

    $data['headingH1'] = 'Восстановние пароля';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/reset', $data));
  }

  private function validate() {
    $length = utf8_strlen(trim($this->request->post['password']));
    if ($length < 4 || $length > 20) $this->errorPassword = 'Пароль должен быть от 4 до 20 символов!';

    if ($this->request->post['confirm'] != $this->request->post['password']) {
      $this->errorConfirm = 'Пароль и подтверждение пароля различны!';
    }

    return !$this->errorPassword && !$this->errorConfirm;
  }
}
