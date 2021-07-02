<?
class ControllerSharedHeaderSearch extends Controller {
  public function index() {
    $data['search'] = $this->catalog->getSearch();
    return $this->load->view('shared/header_search/header_search', $data);
  }

  public function search() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $this->catalog->setSearch($requestData['search'] ?? '');
    header('Content-Type: application/json');

    if (strpos($this->catalog->getSearch(), 'copy') !== false) {
      echo json_encode([]);
      exit();
    }

    $sql = "
      WITH
      tmpProducts AS (
        SELECT
          cd.category_id AS categoryId,
          cd.name AS categoryName,
          pd.product_id AS productId,
          pd.name AS productName,
          COALESCE(
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = {$this->customer->getGroupId()}),
            p.price) AS priceOld,
          IF(p.image = '',
            COALESCE(
              (SELECT image FROM oc_product_image
                WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
              'placeholder.jpg'
            ), p.image) AS image
        FROM oc_product p
        LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
        LEFT JOIN oc_product_to_category ptc ON ptc.product_id = p.product_id
        LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
        LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
        WHERE p.status = 1
          AND cp.level = 1
          AND {$this->catalog->getSearchSQL()}
        GROUP BY categoryId
        ORDER BY p.quantity + p.quantity_store_2 DESC
        LIMIT 10
      ),
      tmpProductsFull AS (
        SELECT
        *,
        COALESCE(
          (SELECT price
            FROM oc_product_special
            WHERE product_id = p.productId
              AND customer_group_id = {$this->customer->getGroupId()}
              AND (date_start = '0000-00-00' OR date_start < NOW())
              AND (date_end = '0000-00-00' OR date_end > NOW())
          ),
          priceOld) AS price
        FROM tmpProducts p
      )
      SELECT
        CONCAT(cp.path_id, '_', p.categoryId) AS path,
        cd.name AS categoryName0,
        p.categoryName,
        p.productId,
        p.productName,
        p.image,
        p.price != p.priceOld AS isPromotions,
        p.price AS priceUSD,
        ROUND(p.price * c.value) AS priceUAH,
        ROUND(p.priceOld * c.value) AS priceOldUAH
        FROM tmpProductsFull p
      LEFT JOIN oc_category_path cp ON cp.category_id = p.categoryId
      LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE cp.level = 0
    ";

    $data = $this->db->query($sql)->rows;

    foreach ($data as &$item) {
      $categoryPath = ['path' => $item['path'], 'search' => $search];
      $item['categoryUrl'] = $this->url->link('search', $categoryPath);
      $item['productUrl'] = $this->url->link('product', ['product_id' => $item['productId']]);
      $item['image'] = $this->image->resize($item['image'], 60, 60);
      unset($item['path']);
      unset($item['productId']);
    }

    echo json_encode($data);
  }
}
