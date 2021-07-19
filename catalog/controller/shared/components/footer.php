<?
class ControllerSharedComponentsFooter extends Controller {
  public function index() {
    $data['libScripts'] = $this->document->getLibScripts();
    $data['customScripts'] = $this->document->getCustomScripts();

    $data['categories'] = $this->getCategories();

    $data['linkAbout'] = $this->url->link('about');
    $data['linkWarranty'] = $this->url->link('information', ['information_id' => 'warranty']);
    $data['linkUserDataUsage'] = $this->url->link('information', ['information_id' => 'user_data_usage']);
    $data['linkContact'] = $this->url->link('information', ['information_id' => 'contact']);

    $data['linkDelivery'] = $this->url->link('information', ['information_id' => 'delivery']);
    $data['linkNews'] = $this->url->link('news_list');
    $data['linkTracking'] = $this->url->link('tracking');

    $data['footerBtnScrollToTop'] = $this->load->controller('shared/components/footer_btn_scroll_to_top');
    $data['mobileMenu'] = $this->load->controller('shared/components/mobile_menu');

    if ($_GET['route'] !== 'checkout') $data['cart'] = $this->load->controller('shared/cart');

    $data['customerPhone'] = $this->customer->getPhone();
    $data['isLogged'] = $this->customer->getId();

    return $this->load->view('shared/components/footer/footer', $data);
  }

  private function getCategories() {
    $sql = "
      SELECT cd.name, c.category_id AS path
      FROM oc_category c
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE c.parent_id = 0 AND c.status = 1
      ORDER BY c.sort_order, cd.name
    ";

    $categories = $this->db->query($sql)->rows;

    foreach ($categories as &$category) {
      $category['link'] = $this->url->link('product/category', ['path' => $category['path']]);
    }
    return $categories;
  }
}
