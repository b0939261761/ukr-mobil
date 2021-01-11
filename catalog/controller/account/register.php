<?
class ControllerAccountRegister extends Controller {
  private $error = [];

  public function index() {
    if ($this->customer->isLogged()) $this->response->redirect($this->url->link('account/account'));

    $data['firstName'] = $this->db->escape($this->request->post['firstName'] ?? '');
    $data['lastName'] = $this->db->escape($this->request->post['lastName'] ?? '');
    $data['email'] = $this->db->escape($this->request->post['email'] ?? '');
    $data['phone'] = $this->db->escape($this->request->post['phone'] ?? '');
    $data['region'] = $this->db->escape($this->request->post['region'] ?? '');
    $data['city'] = $this->db->escape($this->request->post['city'] ?? '');
    $data['warehouse'] = $this->db->escape($this->request->post['warehouse'] ?? '');
    $data['password'] = trim($this->request->post['password'] ?? '');
    $data['confirm'] = trim($this->request->post['confirm'] ?? '');
    $data['captcha'] = $this->load->controller('extension/captcha/google');
    $data['agree'] = $this->request->post['agree'] ?? 0;

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $salt = token(9);

      $sql = "
        INSERT INTO oc_customer (
          firstname, lastname, email, telephone, region,
          city, warehouse, salt,
          password
        ) VALUES (
          '{$data['firstName']}', '{$data['lastName']}', LOWER('{$data['email']}'),
          '{$data['phone']}', '{$data['region']}', '{$data['city']}',
          '{$data['warehouse']}', '{$salt}',
          SHA1(CONCAT('{$salt}', SHA1(CONCAT('{$salt}', SHA1('{$data['password']}')))))
        )
      ";
      $this->db->query($sql);
      $this->sendEmail($data['email']);
      $this->customer->login($data['email'], $data['password']);
      $this->response->redirect($this->url->link('account/success'));
    }

    $data['action'] = $this->url->link('account/register');
    $data['linkAgree'] = $this->url->link('information/information', ['information_id' => 'agree_to_terms']);
    $data['linkLogin'] = $this->url->link('account/login');
    $data['error'] = $this->error;

    $data['headingH1'] = 'Регистрация';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/register', $data));
  }

  private function validate() {
    $lengthFirstName = utf8_strlen(trim($this->request->post['firstName']));
    if ($lengthFirstName < 1 || $lengthFirstName > 32) {
      $this->error['firstName'] = 'Имя должно быть от 1 до 32 символов!';
    }

    $lengthLastName = utf8_strlen(trim($this->request->post['lastName']));
    if ($lengthLastName < 1 || $lengthLastName > 32) {
      $this->error['lastName'] = 'Фамилия должна быть от 1 до 32 символов!';
    }

    $email = $this->db->escape(($this->request->post['email']));
    if (utf8_strlen(trim($email)) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->error['email'] = 'E-Mail введен неправильно!';
    }

    $sql = "SELECT 1 FROM oc_customer WHERE LOWER(email) = LOWER('{$email}')";
    if (!empty($this->db->query($sql)->row)) {
      $this->error['warning'] = 'Данный E-Mail уже зарегистрирован!';
    }

    if (utf8_strlen($this->request->post['phone']) != 12) {
      $this->error['phone'] = 'Телефон должен быть 13 цифр!';
    }

    $lengthPassword = utf8_strlen(trim($this->request->post['password']));
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

  private function sendEmail($email) {
    $configService = new \Ego\Services\ConfigService();
    (new \Ego\Providers\MailProvider())
      ->setTo($email)
      ->setFrom($configService->getEmailAdministratorMain(), $configService->getSiteTitle())
      ->setSubject('UKRMobil - Благодарим за регистрацию')
      ->setView('mails.register')
      ->setBodyData(['linkLogin' => $this->url->link('account/login')])
      ->sendMail();
  }
}
