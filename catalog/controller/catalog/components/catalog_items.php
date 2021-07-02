<?
class ControllerCatalogComponentsCatalogItems extends Controller {
  public function index() {
    // $data['products'] = $this->getProducts();
    $data['products'] = $this->catalog->getItems();
    return $this->load->view('catalog/components/catalog_items/catalog_items', $data);
  }

  private function getProducts() {
    $this->load->model('tool/image');

    $customerGroupId = (int)($this->customer->getGroupId() ?? 1);

    $sql = "
      WITH
      tmpProducts AS (
        SELECT
          p.product_id AS id,
          pd.name,
          p.isLatest,
          p.isSalesLeader,
          IF(p.dateExpected = '0000-00-00', '', DATE_FORMAT(p.dateExpected, '%d.%m.%Y')) AS dateExpected,
          IF(p.image = '',
            COALESCE(
              (SELECT image FROM oc_product_image
                WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
              'placeholder.jpg'
            ),
            p.image) AS image,
          COALESCE(
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = {$customerGroupId}),
            p.price) AS priceOld,
          p.quantity AS quantityStore1,
          p.quantity_store_2 AS quantityStore2
        FROM oc_product p
        LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
        WHERE p.status = 1
        ORDER BY p.date_added DESC, p.product_id DESC
        LIMIT 10
      ),
      tmpProductsFull AS (
        SELECT
        *,
        COALESCE(
          (SELECT price
            FROM oc_product_special
            WHERE product_id = p.id
              AND customer_group_id = {$customerGroupId}
              AND (date_start = '0000-00-00' OR date_start < NOW())
              AND (date_end = '0000-00-00' OR date_end > NOW())
          ),
          priceOld) AS price
        FROM tmpProducts p
      )
      SELECT
        p.id,
        p.name,
        p.image,
        p.isLatest,
        p.isSalesLeader,
        p.dateExpected,
        p.price != p.priceOld AS isPromotions,
        p.quantityStore1,
        p.quantityStore2,
        p.price AS priceUSD,
        ROUND(p.price * c.value) AS priceUAH,
        ROUND(p.priceOld * c.value) AS priceOldUAH
      FROM tmpProductsFull p
      LEFT JOIN oc_currency c ON c.currency_id = 980
    ";

    $products = $this->db->query($sql)->rows;
    foreach ($products as &$product) {
      $product['link'] = $this->url->link('product', ['product_id' => $product['id']]);
      $product['image'] = $this->model_tool_image->resize($product['image'], 306, 306);
    }
    return $products;
  }
}




