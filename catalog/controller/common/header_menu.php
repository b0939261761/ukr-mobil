<?
class ControllerCommonHeaderMenu extends Controller {
  public function index() {
    $data['home'] = $this->url->link('home/home');
    $data['download_price_list'] = $this->url->link('information/price_list');
    $data['service'] = $this->url->link('information/tracking');
    $data['news'] = $this->url->link('information/news');
    $data['about_us'] = $this->url->link('information/about_us');
    $data['about_us_delivery'] = "{$this->url->link('information/about_us')}#delivery";
    $data['about_us_warrantly'] = "{$this->url->link('information/about_us')}#warrantly";
    $data['about_us_contact'] = "{$this->url->link('information/about_us')}#contact";
    $data['expected_income'] = $this->url->link('information/expected_income');
    $data['last_income'] = $this->url->link('information/last_income');

    return $this->load->view('common/header_menu', $data);
  }
}
