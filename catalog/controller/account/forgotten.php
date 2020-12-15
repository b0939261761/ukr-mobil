<?
class ControllerAccountForgotten extends Controller {
  private $warning;

  public function index() {
    if ($this->customer->isLogged()) $this->response->redirect($this->url->link('account/account'));

    if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
      $code = token(40);
      $email = $this->db->escape(($this->request->post['email']));
      $sql = "UPDATE oc_customer SET code = '${code}' WHERE LOWER(email) = LOWER('${email}')";
      $this->db->query($sql);
      $message = 'Новый пароль был выслан на Ваш E-Mail';
      $this->sendEmail($email, $code);
      $redirect = $this->url->link('account/login', ['success' => $message]);
      $this->response->redirect($redirect);
    }

    $data['warning'] = $this->warning;
    $data['action'] = $this->url->link('account/forgotten');
    $data['email'] = $this->request->post['email'] ?? '';

    $data['linkLogin'] = $this->url->link('account/login');
    $data['headingH1'] = 'Восстановние пароля';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/forgotten', $data));
  }

  private function validate() {
    $email = $this->db->escape(($this->request->post['email']));
    $sql = "SELECT status FROM oc_customer WHERE LOWER(email) = LOWER('{$email}')";
    $customer = $this->db->query($sql)->row;

    if (empty($customer)) $this->warning = 'E-Mail адрес не найден, проверьте и попробуйте еще раз!';
    elseif (!$customer['status']) $this->warning = 'Внимание! Ваш аккаунт еще не активирован.';
    return !$this->warning;
  }

  private function sendEmail($email, $code) {
    $configService = new \Ego\Services\ConfigService();
    (new \Ego\Providers\MailProvider())
      ->setTo($email)
      ->setFrom($configService->getEmailAdministratorMain(), $configService->getSiteTitle())
      ->setSubject('UkrMobil - Восcтановление пароля')
      ->setView('mails.forgotten')
      ->setBodyData([
        'linkReset' => $this->url->link('account/reset', ['code' => $code]),
        'ip'        => $this->request->server['REMOTE_ADDR']
      ])
      ->sendMail();
  }
}
