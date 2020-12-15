<?
class ControllerCommonFooter extends Controller {
  public function index() {
    $data['linkSitemap'] = $this->url->link('information/sitemap');
    $data['footer_newsletter'] = $this->load->controller('common/footer_newsletter');
    return $this->load->view('common/footer', $data);
  }
}
