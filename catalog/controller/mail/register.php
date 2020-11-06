<?
class ControllerMailRegister extends Controller {
  public function index(&$route, &$args, &$output) {
    $data['linkLogin'] = $this->url->link('account/login');

    $mail = new Mail($this->config->get('config_mail_engine'));
    $mail->parameter = $this->config->get('config_mail_parameter');
    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

    $mail->setTo($args[0]['email']);
    $mail->setFrom($this->config->get('config_email'));
    $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
    $mail->setSubject('UKRMobil - Благодарим за регистрацию');
    $mail->setText($this->load->view('mail/register', $data));
    $mail->send();
  }
}
