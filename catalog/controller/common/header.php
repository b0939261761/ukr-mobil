<?
class ControllerCommonHeader extends Controller {
  public function index() {
    header('Cache-Control: no-store');
    $data['base'] = $this->request->request['domain'];
    $this->document->addLink($this->request->request['canonical'], 'canonical');

    $data['title'] = $this->document->getTitle();
    $data['description'] = $this->document->getDescription();
    $data['keywords'] = $this->document->getKeywords();
    $data['metaList'] = $this->document->getMetaList();
    $data['microdataList'] = $this->document->getMicrodata();
    $data['dataLayer'] = $this->document->getDataLayer();
    $data['links'] = $this->document->getLinks();
    $data['styles'] = $this->document->getStyles();
    $data['scripts'] = $this->document->getScripts('header');

    $data['isLogged'] = $this->customer->isLogged();

    if ($data['isLogged']) {
      $data['customerName'] = "{$this->customer->getFirstName()} {$this->customer->getLastName()}";
      $sql = "SELECT balance FROM oc_customer WHERE customer_id = {$this->customer->getId()}";
      $data['balance'] = $this->db->query($sql)->row['balance'];
    }

    $data['linkHome'] = $this->url->link('common/home');
    $data['login'] = $this->url->link('account/login');
    $data['logout'] = $this->url->link('account/logout');
    $data['accountDocuments'] = "{$this->url->link('account/account')}#documents";
    $data['accountOrders'] = "{$this->url->link('account/account')}#orders";
    $data['accountProfile'] = "{$this->url->link('account/account')}#profile";
    $data['accountTerms'] = "{$this->url->link('account/account')}#terms";

    $data['search'] = $this->load->controller('common/search');

    if (!isset($this->request->get['route']) || $this->request->get['route'] != 'checkout/cart') {
      $data['cart'] = $this->load->controller('common/cart');
    }

    $data['headerMenu'] = $this->load->controller('common/header_menu');
    $data['headerCategory'] = $this->load->controller('common/header_category');

    $sql = "SELECT value FROM oc_currency WHERE currency_id = 980";
    $data['rate'] = $this->db->query($sql)->row['value'] ?? 0;
    return $this->load->view('common/header', $data);
  }
}
