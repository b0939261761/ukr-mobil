<?
class ControllerSharedComponentsMobileMenu extends Controller {
  public function index() {
    $categories = $this->getCategories();
    $data['categories'] = $this->loopCategory($categories);
    $data['navCategories'] = $this->load->controller('shared/components/nav_catalog');

    $data['linkAbout'] = $this->url->link('about');
    $data['linkNews'] = $this->url->link('news_list');
    $data['linkTracking'] = $this->url->link('tracking');
    $data['linkDelivery'] = $this->url->link('information', ['information_id' => 'delivery']);
    $data['linkIncome'] = $this->url->link('income');
    $data['linkContact'] = $this->url->link('information', ['information_id' => 'contact']);
    $data['linkPriceList'] = $this->url->link('price_list');

    $data['isLogged'] = $this->customer->getId();
    $data['linkAccount'] = $this->url->link('account');
    $data['linkCheckout'] = $this->url->link('checkout');

    $data['currency'] = $this->main->getCurrency();
    return $this->load->view('shared/components/mobile_menu/mobile_menu', $data);
  }

  private function loopCategory ($categories) {
    foreach ($categories as &$category) {
      $category['link'] = $this->url->link('product/category', ['path' => $category['path']]);
      $children = $category['children'];
      if (count($children)) {
        $category['children'] = $this->loopCategory($children);
      }
    }
    return $categories;
  }

  private function getCategories() {
    $sql = "
      SELECT
        IF(COUNT(path), JSON_ARRAYAGG(JSON_OBJECT('path', path, 'name', name, 'children', children)), JSON_ARRAY()) AS value
      FROM (
        SELECT
          c1.category_id AS path,
          cd1.name,
          IF(COUNT(t2.path), JSON_ARRAYAGG(JSON_OBJECT('path', t2.path, 'name', t2.name, 'children', t2.children)), JSON_ARRAY()) AS children
          FROM oc_category c1
        LEFT JOIN oc_category_description cd1 ON cd1.category_id = c1.category_id
        LEFT JOIN LATERAL (
          SELECT
            CONCAT(c1.category_id, '_', c2.category_id) AS path,
            cd2.name,
            IF(COUNT(t3.path), JSON_ARRAYAGG(JSON_OBJECT('path', t3.path, 'name', t3.name, 'children', t3.children)), JSON_ARRAY()) AS children
          FROM oc_category c2
          LEFT JOIN oc_category_description cd2 ON cd2.category_id = c2.category_id
          LEFT JOIN LATERAL (
            SELECT
              CONCAT(c1.category_id, '_', c2.category_id, '_', c3.category_id) AS path,
              cd3.name,
              JSON_ARRAY() AS children
            FROM oc_category c3
            LEFT JOIN oc_category_description cd3 ON cd3.category_id = c3.category_id
            WHERE c3.parent_id = c2.category_id AND c3.status = 1
            ORDER BY c3.sort_order, cd3.name
          ) AS t3 ON true
          WHERE c2.parent_id = c1.category_id AND c2.status = 1
          GROUP BY c2.category_id
          ORDER BY c2.sort_order, cd2.name
        ) AS t2 ON true
        WHERE c1.parent_id = 0 AND c1.status = 1
        GROUP BY c1.category_id
        ORDER BY c1.sort_order, cd1.name
      ) AS t1
    ";
    return json_decode($this->db->query($sql)->row['value'], true);
  }
}
