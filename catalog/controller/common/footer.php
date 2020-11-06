<?
class ControllerCommonFooter extends Controller {
  public function index() {
    $data['home'] = $this->url->link('common/home');
    $data['sitemap'] = $this->url->link('information/sitemap');
    $data['ego_newsletter'] = $this->load->controller('extension/module/ego_newsletter');
    $data['isLogged'] = $this->customer->isLogged() ? true : false;
    $data['name'] = $this->config->get('config_name');
    $data['logo'] = "/image/{$this->config->get('config_logo')}";
    return $this->load->view('common/footer', $data);
  }
}
