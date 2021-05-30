<?
class ControllerSharedComponentsFooter extends Controller {
  public function index() {
    $data['libScripts'] = $this->document->getLibScripts();
    $data['customScripts'] = $this->document->getCustomScripts();

    $data['categories'] = $this->getCategories();

    $data['linkAboutUs'] = $this->url->link('information/about_us');
    $data['linkWarrantly'] = "{$this->url->link('information/about_us')}#warrantly";
    $data['linkUserDataUsage'] = $this->url->link('information/information', ['information_id' => 'user_data_usage']);
    $data['linkContacts'] = "{$this->url->link('information/about_us')}#contact";

    $data['linkDelivery'] = "{$this->url->link('information/about_us')}#delivery";
    $data['linkNews'] = $this->url->link('information/news');
    $data['linkTracking'] = $this->url->link('tracking');

    $data['footerBtnScrollToTop'] = $this->load->controller('shared/components/footer_btn_scroll_to_top');
    $data['mobileMenu'] = $this->load->controller('shared/components/mobile_menu');

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
