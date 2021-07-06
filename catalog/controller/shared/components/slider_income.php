<?
class ControllerSharedComponentsSliderIncome extends Controller {
  public function index() {
    $customerGroupId = $this->customer->getGroupId();

    $sql = "
      WITH
      tmpProducts AS (
        SELECT
          p.product_id AS id,
          pd.name,
          IF(image = '',
            COALESCE(
              (SELECT image FROM oc_product_image
                WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
              'placeholder.jpg'
            ),
            image) AS image,
          COALESCE(
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = {$customerGroupId}
              ORDER BY priority ASC, price ASC LIMIT 1),
            p.price) AS priceOld,
          p.quantity AS quantityStore1,
          p.quantity_store_2 AS quantityStore2
        FROM oc_product p
        LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
        WHERE p.status = 1
        ORDER BY p.date_added DESC, pd.name
        LIMIT 10
      ),
      tmpProductsFull AS (
        SELECT
        *,
        COALESCE(
          (SELECT price
            FROM oc_product_special
            WHERE product_id = p.id
              AND customer_group_id = 1
              AND (date_start = '0000-00-00' OR date_start < NOW())
              AND (date_end = '0000-00-00' OR date_end > NOW())
            ORDER BY priority ASC, price ASC LIMIT 1),
          priceOld) AS price
        FROM tmpProducts p
      )
      SELECT
        p.id,
        p.name,
        p.image,
        p.quantityStore1,
        p.quantityStore2,
        p.quantityStore1 + p.quantityStore2 AS quantity,
        p.price AS priceUSD,
        ROUND(p.price * c.value) AS priceUAH,
        IF(p.price = p.priceOld, 100, ROUND(p.priceOld * c.value)) AS priceOldUAH
      FROM tmpProductsFull p
      LEFT JOIN oc_currency c ON c.currency_id = 980
    ";

    $data['products'] = $this->db->query($sql)->rows;
    foreach ($data['products'] as &$product) {
      $product['link'] = $this->url->link('product', ['product_id' => $product['id']]);
      $product['image'] = $this->image->resize($product['image'], 306, 306);
    }

    $data['linkIncome'] = $this->url->link('income/income');
    $data['isLogged'] = $this->customer->getId();
    return $this->load->view('shared/components/slider_income/slider_income', $data);
  }
}
