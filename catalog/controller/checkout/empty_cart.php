<?
class ControllerCheckoutEmptyCart extends Controller {
  public function index() {
    $data['headingH1'] = 'Пустая корзина';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['linkHome'] = $this->url->link('home/home');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('checkout/empty_cart', $data));
  }
}
