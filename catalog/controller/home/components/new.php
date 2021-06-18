<?
class ControllerHomeComponentsNew extends Controller {
  public function index() {
    $data['products'] = $this->getProducts();
    return $this->load->view('home/components/new/new', $data);
  }

  private function getProducts() {
    $customerGroupId = $this->customer->getGroupId();

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
              WHERE product_id = p.product_id AND customer_group_id = {$this->customer->getGroupId()}),
            p.price) AS priceOld,
          p.quantity AS quantityStore1,
          p.quantity_store_2 AS quantityStore2,
          pgp.priceMin,
          pgp.priceMax,
          IF(COUNT(prop.name),
            JSON_ARRAYAGG(JSON_OBJECT(
              'ord', prop.ord, 'name', prop.name, 'values', prop.`values`, 'isColor', isColor
            )),
            JSON_ARRAY()
          ) AS properties
        FROM oc_product p
        LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
        LEFT JOIN product_group_prices pgp ON pgp.product_group_id = p.product_group_id
          AND customer_group_id = {$this->customer->getGroupId()}
        LEFT JOIN LATERAL (
          SELECT
            ppr.name, ppr.ord, ppr.name = 'Цвет' AS isColor,
            JSON_ARRAYAGG(JSON_OBJECT(
              'ord', ppv.ord, 'name', ppv.name, 'color', ppv.color,
              'isActive', prpr.product_id_link = p.product_id,
              'id', prpr.product_id_link
              )) AS `values`
          FROM products_properties prpr
          LEFT JOIN product_property_values ppv ON ppv.id = prpr.product_property_value_id
          LEFT JOIN product_properties ppr ON ppr.id = ppv.product_property_id
          WHERE prpr.product_id = p.product_id
          GROUP BY ppr.id
        ) prop ON true
        WHERE p.status = 1
        GROUP BY p.product_id
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
              AND customer_group_id = {$this->customer->getGroupId()}
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
        p.quantityStore1 + p.quantityStore2 AS quantity,
        p.price AS priceUSD,
        ROUND(p.price * c.value) AS priceUAH,
        ROUND(p.priceOld * c.value) AS priceOldUAH,
        p.priceMin AS priceMinUSD,
        p.priceMax AS priceMaxUSD,
        ROUND(p.priceMin * c.value) AS priceMinUAH,
        ROUND(p.priceMax * c.value) AS priceMaxUAH,
        p.properties
      FROM tmpProductsFull p
      LEFT JOIN oc_currency c ON c.currency_id = 980
    ";

    file_put_contents('./catalog/controller/startup/__LOG__.txt', $sql);


    $items = $this->db->query($sql)->rows;

    foreach ($items as &$item) {
      $item['link'] = $this->url->link('product/product', ['product_id' => $item['id']]);
      $item['image'] = $this->image->resize($item['image'], 306, 306);

      $item['properties'] = json_decode($item['properties'], true);
      uasort($item['properties'], function ($a, $b) { return $a['ord'] - $b['ord']; });

      foreach ($item['properties'] as &$property) {
        uasort($property['values'], function ($a, $b) { return $a['ord'] - $b['ord']; });

        foreach ($property['values'] as &$value) {
          if (!$value['isActive']) $value['link'] = $this->url->link('product/product', ['product_id' => $value['id']]);
        }
      }
    }

    return $items;
  }
}
