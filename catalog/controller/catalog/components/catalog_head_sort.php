<?
class ControllerCatalogComponentsCatalogHeadSort extends Controller {
  public function index() {
    $data['catalogSort'] = $this->load->controller('catalog/components/catalog_sort');
    return $this->load->view('catalog/components/catalog_head_sort/catalog_head_sort', $data);
  }
}




