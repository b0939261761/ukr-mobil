<?
class ControllerHomeComponentsPromotions extends Controller {
  public function index() {
    $data['products'] = $this->getProducts();
    return $this->load->view('home/components/promotions/promotions', $data);
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
              'placeholder.png'
            ),
            p.image) AS image,
          COALESCE(
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = {$customerGroupId}),
            p.price) AS priceOld,
          ps.price,
          p.quantity AS quantityStore1,
          p.quantity_store_2 AS quantityStore2
          FROM oc_product_special ps
          INNER JOIN oc_product p ON p.product_id = ps.product_id
          LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
            AND ps.customer_group_id = {$customerGroupId}
            AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW())
            AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())
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
      FROM tmpProducts p
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE p.price != p.priceOld
    ";

    $products = $this->db->query($sql)->rows;
    foreach ($products as &$product) {
      $product['link'] = $this->url->link('product/product', ['product_id' => $product['id']]);
      $product['image'] = $this->model_tool_image->resize($product['image'], 306, 306);
    }
    return $products;
  }
}
