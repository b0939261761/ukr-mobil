<?
class ControllerSharedComponentsHeaderBottom extends Controller {
  public function index() {
    $data['isLogged'] = $this->customer->getId();
    $data['linkAccount'] = $this->url->link('account');
    $data['linkCheckout'] = $this->url->link('checkout');
    $data['cartCount'] = $this->load->controller('shared/cart/getCount');
    $data['headerSearch'] = $this->load->controller('shared/header_search');
    return $this->load->view('shared/components/header_bottom/header_bottom', $data);
  }
}
