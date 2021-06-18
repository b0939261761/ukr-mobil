<?
class ControllerStartupStartup extends Controller {
  public function index() {
    $this->registry->set('url', new Url(HTTP_SERVER, HTTPS_SERVER));

    $this->registry->set('customer', new Cart\Customer($this->registry));
    $this->registry->set('image', new Image());
    $this->registry->set('mail', new Mail());
    $this->registry->set('main', new Cart\Main($this->registry));
    $this->registry->set('catalog', new Cart\Catalog($this->registry));

    $secure = $_SERVER["HTTPS"] ? true : false;
    setcookie($this->config->get('session_name'), $this->session->getId(), 0, '/', '', $secure, true);
  }
}
