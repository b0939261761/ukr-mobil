<?
class ControllerExtensionCaptchaGoogle extends Controller {
  public function index($error = []) {
    $data['he'] = $error['captcha'] ?? '';
    $data['site_key'] = $this->config->get('captcha_google_key');
    $data['route'] = $this->request->get['route'];
    return $this->load->view('extension/captcha/google', $data);
  }

    public function validate() {
    if (empty($this->session->data['gcapcha'])) {
      if (!isset($this->request->post['g-recaptcha-response'])) {
        return 'Проверочный код не совпадает с изображением!';
      }

      $recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->config->get('captcha_google_secret')) . '&response=' . $this->request->post['g-recaptcha-response'] . '&remoteip=' . $this->request->server['REMOTE_ADDR']);
      $recaptcha = json_decode($recaptcha, true);

      if ($recaptcha['success']) {
        $this->session->data['gcapcha']  = true;
      } else {
        return 'Проверочный код не совпадает с изображением!';
      }
    }
  }
}
