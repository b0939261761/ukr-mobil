<?
class ControllerCommonFooter extends Controller {
  public function index() {
    $data['linkSitemap'] = $this->url->link('information/sitemap');
    $data['linkUserDataUsage'] = $this->url->link('information/information',
      ['information_id' => 'user_data_usage']);
    $data['linkOffer'] = $this->url->link('information/information', ['information_id' => 'offer']);
    $data['footerNewsletter'] = $this->load->controller('common/footer_newsletter');

    return $this->load->view('common/footer', $data);
  }
}
