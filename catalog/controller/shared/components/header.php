<?
class ControllerSharedComponentsHeader extends Controller {
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
    $data['preloads'] = $this->document->getPreloads();
    $data['libStyles'] = $this->document->getLibStyles();
    $data['customStyles'] = $this->document->getCustomStyles();



    // $data['isLogged'] = $this->customer->isLogged();

    // if ($data['isLogged']) {
    //   $data['customerName'] = "{$this->customer->getFirstName()} {$this->customer->getLastName()}";
    //   $sql = "SELECT balance FROM oc_customer WHERE customer_id = {$this->customer->getId()}";
    //   $data['balance'] = $this->db->query($sql)->row['balance'];
    // }


    // if (!isset($this->request->get['route']) || $this->request->get['route'] != 'checkout/cart') {
    //   $data['cart'] = $this->load->controller('common/cart');
    // }

    // ----------------------------------------------

    $data['headerTop'] = $this->load->controller('shared/components/header_top');
    $data['headerBottom'] = $this->load->controller('shared/components/header_bottom');

    return $this->load->view('shared/components/header/header', $data);
  }
}
