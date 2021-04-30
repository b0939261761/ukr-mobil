<?
class ControllerSharedComponentsHeaderTop extends Controller {
  public function index() {
    $data['linkAboutUs'] = $this->url->link('information/about_us');
    $data['linkNews'] = $this->url->link('information/news');
    $data['linkTracking'] = $this->url->link('information/tracking');
    $data['linkDelivery'] = "{$this->url->link('information/about_us')}#delivery";
    $data['linkIncome'] = $this->url->link('income/income');
    $data['linkContacts'] = "{$this->url->link('information/about_us')}#contact";
    $data['linkPriceList'] = $this->url->link('information/price_list');

    $sql = "SELECT value FROM oc_currency WHERE currency_id = 980";
    $data['rate'] = $this->db->query($sql)->row['value'] ?? 0;

    return $this->load->view('shared/components/header_top/header_top', $data);
  }
}
