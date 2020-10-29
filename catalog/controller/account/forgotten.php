<?
class ControllerAccountForgotten extends Controller {
  private $warning;

  public function index() {
    if ($this->customer->isLogged()) $this->response->redirect($this->url->link('account/account'));

    $this->load->model('account/customer');
    $data['linkLogin'] = $this->url->link('account/login');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_account_customer->editCode($this->request->post['email'], token(40));
      $this->session->data['success'] = 'Новый пароль был выслан на Ваш E-Mail.';
      $this->response->redirect($data['linkLogin']);
    }

    $data['warning'] = $this->warning;
    $data['action'] = $this->url->link('account/forgotten');
    $data['email'] = $this->request->post['email'] ?? '';

    $data['headingH1'] = 'Восстановние пароля';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/forgotten', $data));
  }

  private function validate() {
    if (!isset($this->request->post['email']) ||
        !$this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
      $this->warning = 'E-Mail адрес не найден, проверьте и попробуйте еще раз!';
      return false;
    }

    $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
    if ($customer_info && !$customer_info['status']) {
      $this->warning = 'Внимание! Ваш аккаунт еще не активирован.';
    }

    return !$this->warning;
  }
}
