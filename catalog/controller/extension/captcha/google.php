<?
class ControllerExtensionCaptchaGoogle extends Controller {
  public function index() {
    $data['siteKey'] = $this->config->get('captcha_google_key');
    return $this->load->view('extension/captcha/google', $data);
  }

  public function validate() {
    $query = http_build_query([
      'secret'   => $this->config->get('captcha_google_secret'),
      'response' => $this->request->post['g-recaptcha-response'],
      'remoteip' => $this->request->server['REMOTE_ADDR']
    ]);
    $recaptcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?{$query}");
    $recaptcha = json_decode($recaptcha, true);
    return $recaptcha['success'] ? '' : 'Ошибка решения капчи!';
  }
}
