<?
use Ego\Controllers\BaseController;

class ControllerAccountLogin extends BaseController {
  private $error = [];

  public function index() {
    $this->load->model('account/customer');

    if (!empty($this->request->get['token'])) {
      $this->customer->logout();

      $sessionId = $this->db->escape($this->session->getId());
      $sql = "DELETE FROM oc_cart WHERE customer_id = 0 AND session_id = '{$sessionId}'";
      $this->db->query($sql);

      $customer_info = $this->model_account_customer->getCustomerByToken($this->request->get['token']);
      if ($customer_info && $this->customer->login($customer_info['email'], '', true)) {
        $this->response->redirect($this->url->link('account/account'));
      }
    }

    if ($this->customer->isLogged()) $this->response->redirect($this->url->link('account/account'));

    if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {

      // Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
      if (isset($this->request->post['redirect']) && $this->request->post['redirect'] != $this->url->link('account/logout', '', true) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
        $this->response->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
      } else {
        $this->response->redirect($this->url->link('account/account', '', true));
      }
    }

    $data['action'] = $this->url->link('account/login');
    $data['register'] = $this->url->link('account/register');
    $data['forgotten'] = $this->url->link('account/forgotten');

    // Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
    if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
      $data['redirect'] = $this->request->post['redirect'];
    } elseif (isset($this->session->data['redirect'])) {
      $data['redirect'] = $this->session->data['redirect'];

      unset($this->session->data['redirect']);
    } else {
      $data['redirect'] = '';
    }

    $data['email'] = $this->request->post['email'] ?? '';
    $data['password'] = $this->request->post['password'] ?? '';
    $data['success'] = $this->request->get['success'] ?? '';
    $data['error_warning'] = $this->request->get['error'] ?? '';
    if (!$data['error_warning']) $data['error_warning'] = $this->error['warning'] ?? '';

    $this->document->setTitle('Авторизация');
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/login', $data));
  }

  protected function validate() {
    // Check how many login attempts have been made.
    $login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

    if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
      $this->error['warning'] = 'Вы превысили максимальное количество попыток авторизации. Повторите попытку авторизации на сайте через 1 час';
    }

    // Check if customer has been approved.
    $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

    if ($customer_info && !$customer_info['status']) {
      $this->error['warning'] = 'Необходимо подтвердить аккаунт перед авторизацией.';
    }

    if (!$this->error) {
      if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
        $this->error['warning'] = 'Неправильно заполнены поле E-Mail и/или пароль!';

        $this->model_account_customer->addLoginAttempt($this->request->post['email']);
      } else {
        $this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
      }
    }

    return !$this->error;
  }

}
