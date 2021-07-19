<?
class ControllerSharedComponentsHeaderBottom extends Controller {
  public function index() {
    $data['isLogged'] = $this->customer->getId();
    if ($data['isLogged']) {
      $data['linkAccount'] = $this->url->link('account');
      $data['linkAccountFavorites'] = "{$this->url->link('account')}#favorites";
    }

    if ($_GET['route'] === 'checkout') {
      $data['isHideCart'] = true;
    } else {
      $data['linkCheckout'] = $this->url->link('checkout');
      $data['cartCount'] = $this->load->controller('shared/cart/getCount');
    }

    $data['headerSearch'] = $this->load->controller('shared/header_search');
    return $this->load->view('shared/components/header_bottom/header_bottom', $data);
  }
}
