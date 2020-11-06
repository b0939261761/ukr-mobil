<?
class ControllerAccountRegister extends Controller {
  private $error = [];

  public function index() {
    if ($this->customer->isLogged()) $this->response->redirect($this->url->link('account/account'));

    $this->load->model('account/customer');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $customerId = $this->model_account_customer->addCustomer($this->request->post);

      $customerModel = new \Ego\Models\Customer();
      $customerRow = $customerModel->get($customerId, true);

      if (!empty($customerRow)) {
        $customerRow
          ->setRegion($this->request->post['region'])
          ->setCity($this->request->post['city'])
          ->setWarehouse($this->request->post['warehouse']);

        $customerModel->update($customerRow);
      }

      $this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
      $this->customer->login($this->request->post['email'], $this->request->post['password']);
      $this->response->redirect($this->url->link('account/success'));
    }

    $data['action'] = $this->url->link('account/register');
    $data['linkAgree'] = $this->url->link('information/agree_to_terms');

    $data['firstname'] = $this->request->post['firstname'] ?? '';
    $data['lastname'] = $this->request->post['lastname'] ?? '';
    $data['email'] = $this->request->post['email'] ?? '';
    $data['telephone'] = $this->request->post['telephone'] ?? '';
    $data['region'] = $this->request->post['region'] ?? '';
    $data['city'] = $this->request->post['city'] ?? '';
    $data['warehouse'] = $this->request->post['warehouse'] ?? '';
    $data['password'] = $this->request->post['password'] ?? '';
    $data['confirm'] = $this->request->post['confirm'] ?? '';
    $data['captcha'] = $this->load->controller('extension/captcha/google');
    $data['agree'] = $this->request->post['agree'] ?? false;
    $data['error'] = $this->error;

    $data['headingH1'] = 'Регистрация';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/register', $data));
  }

  private function validate() {
    $lengthFirstName = utf8_strlen(trim($this->request->post['firstname']));
    if ($lengthFirstName < 1 || $lengthFirstName > 32) {
      $this->error['firstName'] = 'Имя должно быть от 1 до 32 символов!';
    }

    $lengthLastName = utf8_strlen(trim($this->request->post['lastname']));
    if ($lengthLastName < 1 || $lengthLastName > 32) {
      $this->error['lastName'] = 'Фамилия должна быть от 1 до 32 символов!';
    }

    if (utf8_strlen(trim($this->request->post['email'])) > 96
        || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
      $this->error['email'] = 'E-Mail введен неправильно!';
    }

    if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
      $this->error['warning'] = 'Данный E-Mail уже зарегистрирован!';
    }

    if (utf8_strlen($this->request->post['telephone']) != 12) {
      $this->error['telephone'] = 'Телефон должен быть 13 цифр!';
    }

    $lengthPassword = utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8'));
    if ($lengthPassword < 4 || $lengthPassword > 40) {
      $this->error['password'] = 'В пароле должно быть от 4 до 20 символов!';
    }

    if ($this->request->post['confirm'] != $this->request->post['password']) {
      $this->error['confirm'] = 'Пароли и пароль подтверждения не совпадают!';
    }

    $captcha = $this->load->controller('extension/captcha/google/validate');
    if ($captcha) $this->error['captcha'] = $captcha;

    if (empty($this->request->post['agree'])) {
      $this->error['warning'] = 'Вы должны прочитать и согласится с Политика конфиденциальности!';
    }

    return !$this->error;
  }
}
