<?
class ControllerAccountReset extends Controller {
  private $errorPassword = '';
  private $errorConfirm = '';

  public function index() {
    if ($this->customer->isLogged()) $this->response->redirect($this->url->link('account/account'));

    $this->load->model('account/customer');
    $code = $this->request->get['code'] ?? '';
    $data['linkLogin'] = $this->url->link('account/login');
    $customer_info = $this->model_account_customer->getCustomerByCode($code);

    if ($customer_info) {
      if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
        $this->model_account_customer->editPassword($customer_info['email'], $this->request->post['password']);
        $this->session->data['success'] = 'Ваш пароль изменен.';
        $this->response->redirect($data['linkLogin']);
      }

      $data['action'] = $this->url->link('account/reset', ['code' => $code]);
      $data['password'] = $this->request->post['password'] ?? '';
      $data['confirm'] = $this->request->post['confirm'] ?? '';
      $data['errorPassword'] = $this->errorPassword;
      $data['errorConfirm'] = $this->errorConfirm;

      $data['headingH1'] = 'Восстановние пароля';
      $this->document->setTitle($data['headingH1']);
      $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
      $data['header'] = $this->load->controller('common/header');
      $data['footer'] = $this->load->controller('common/footer');
      $this->response->setOutput($this->load->view('account/reset', $data));
    } else {
      $this->session->data['error'] = 'Код восстановления пароля ошибочный!';
      $this->response->redirect($data['linkLogin']);
    }
  }

  private function validate() {
    $length = utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8'));
    if ($length < 4 || $length > 20) {
      $this->errorPassword = 'Пароль должен быть от 4 до 20 символов!';
      return false;
    }

    if ($this->request->post['confirm'] != $this->request->post['password']) {
      $this->errorConfirm = 'Пароль и подтверждение пароля различны!';
      return false;
    }

    return true;
  }
}
