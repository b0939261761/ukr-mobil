<?
class ControllerCommonFooter extends Controller {
  public function index() {
    $data['linkSitemap'] = $this->url->link('information/sitemap');
    $data['footer_newsletter'] = $this->load->controller('common/footer_newsletter');
    $data['logoTitle'] = $this->config->get('config_name');
    $data['logoImage'] = "/image/{$this->config->get('config_logo')}";
    return $this->load->view('common/footer', $data);
  }
}
