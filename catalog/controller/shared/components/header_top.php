<?
class ControllerSharedComponentsHeaderTop extends Controller {
  public function index() {
    $data['linkAbout'] = $this->url->link('about');
    $data['linkNews'] = $this->url->link('news_list');
    $data['linkTracking'] = $this->url->link('tracking');
    $data['linkDelivery'] = $this->url->link('information', ['information_id' => 'delivery']);
    $data['linkIncome'] = $this->url->link('income/income');
    $data['linkContact'] = $this->url->link('information', ['information_id' => 'contact']);
    $data['linkPriceList'] = $this->url->link('price_list');

    $data['currency'] = $this->main->getCurrency();
    return $this->load->view('shared/components/header_top/header_top', $data);
  }
}
