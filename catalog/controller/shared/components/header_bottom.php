<?
class ControllerSharedComponentsHeaderBottom extends Controller {
  public function index() {
    $data['isLogged'] = $this->customer->getId();
    $data['linkAccount'] = $this->url->link('account');
    $data['linkCheckout'] = $this->url->link('checkout');

    return $this->load->view('shared/components/header_bottom/header_bottom', $data);
  }
}
