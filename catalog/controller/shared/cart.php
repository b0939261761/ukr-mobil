<?
class ControllerSharedCart extends Controller {
  public function index() {
    return $this->load->view('shared/cart/cart', $this->getData());
  }

  public function getData() {
    $sql = "
      WITH
        tmpProducts AS (
          SELECT
            c.product_id AS id,
            pd.name,
            p.quantity AS quantityStore1,
            p.quantity_store_2 AS quantityStore2,
            COALESCE(
              (SELECT price FROM oc_product_discount
                WHERE product_id = p.product_id AND customer_group_id = {$this->customer->getGroupId()}),
              p.price) AS priceOld,
            c.quantity,
            IF(p.image = '',
              COALESCE(
                (SELECT image FROM oc_product_image
                  WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
                'placeholder.jpg'
              ), p.image) AS image
          FROM oc_cart c
          LEFT JOIN oc_product p ON p.product_id = c.product_id
          LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
          WHERE c.session_id = '{$this->session->getId()}' AND c.customer_id = {$this->customer->getId()}
        ),
        tmpProductsFull AS (
          SELECT
          *,
          quantityStore1 >= quantity AS enoughQuantityStore1,
          quantityStore2 >= quantity AS enoughQuantityStore2,
          quantityStore1 + quantityStore2 AS quantityStore,
          IF (quantity > quantityStore1, quantityStore1, quantity) AS quantityOrder1,
          IF (quantity > quantityStore1,
            IF (quantityStore2 > quantity - quantityStore1, quantity - quantityStore1, quantityStore2),
            0
          ) AS quantityOrder2,
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
          p.quantity,
          p.quantityStore,
          p.enoughQuantityStore1,
          p.enoughQuantityStore2,
          p.image,
          p.price = p.priceOld AS isPromotions,
          ROUND(p.price * p.quantity, 2) AS totalUSD,
          ROUND(p.price * p.quantityOrder1, 2) AS totalOrder1USD,
          ROUND(p.price * p.quantityOrder2, 2) AS totalOrder2USD,
          ROUND(p.price * p.quantity * c.value) AS totalUAH,
          p.price AS priceUSD,
          ROUND(p.price * c.value) AS priceUAH,
          ROUND(p.priceOld * c.value) AS priceOldUAH
        FROM tmpProductsFull p
        LEFT JOIN oc_currency c ON c.currency_id = 980
        ORDER BY p.name
    ";

    // file_put_contents('./catalog/controller/startup/__LOG__.txt',  $sql);


    $data['enoughQuantityStore1'] = true;
    $data['enoughQuantityStore2'] = true;

    $data['products'] = $this->db->query($sql)->rows;
    foreach ($data['products'] as &$product) {
      if (!$product['enoughQuantityStore1']) $data['enoughQuantityStore1'] = false;
      if (!$product['enoughQuantityStore2']) $data['enoughQuantityStore2'] = false;
      $data['totalUSD'] += $product['totalUSD'];
      $totalOrder1USD += $product['totalOrder1USD'];
      $totalOrder2USD += $product['totalOrder2USD'];
      $data['totalUAH'] += $product['totalUAH'];
      $data['quantity'] += $product['quantity'];
      $product['image'] = $this->image->resize($product['image'], 90, 110);
      $product['link'] = $this->url->link('product', ['product_id' => $product['id']]);
    }

    $data['commissionUAH'] = $this->getCommissionAll($totalOrder1USD, $totalOrder2USD);
    $data['totalUSD'] = number_format($data['totalUSD'], 2, '.', '');
    $data['linkCheckout'] = $this->url->link('checkout');
    return $data;
  }

  public function get() {
    echo $this->index();
  }

  public function clear() {
    $sql = "
      DELETE FROM oc_cart
      WHERE session_id = '{$this->session->getId()}'
        AND customer_id = {$this->customer->getId()}
    ";
    $this->db->query($sql);
    exit();
  }

  public function add() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $id = (int)($requestData['id'] ?? 0);
    $quantity = (int)($requestData['quantity'] ?? 0);

    if (!$id) {
      http_response_code(400);
      exit('INVALID');
    }

    $sql = "
      SELECT
        p.quantity + p.quantity_store_2 AS quantityStore,
        COALESCE(c.quantity, 0) AS quantity,
        COALESCE(
          (SELECT price
            FROM oc_product_special
            WHERE product_id = p.product_id
              AND customer_group_id = {$this->customer->getGroupId()}
              AND (date_start = '0000-00-00' OR date_start < NOW())
              AND (date_end = '0000-00-00' OR date_end > NOW())
          ),
          (SELECT price FROM oc_product_discount
            WHERE product_id = p.product_id AND customer_group_id = {$this->customer->getGroupId()}),
          p.price
        ) AS price
      FROM oc_product p
      LEFT JOIN oc_cart c ON c.product_id = p.product_id
        AND c.session_id = '{$this->session->getId()}'
        AND c.customer_id = {$this->customer->getId()}
      WHERE p.product_id = {$id}
    ";


    $product = $this->db->query($sql)->row;

    if (!$product) {
      http_response_code(400);
      exit('NOT_EXISTS');
    }

    $quantity = $product['quantity'] + $quantity;
    if ($quantity > $product['quantityStore']) {
      http_response_code(400);
      exit('MAX_QUANTITY');
    }

    if ($quantity < 1) {
      http_response_code(400);
      exit('MIN_QUANTITY');
    }

    $sql = "
      INSERT INTO oc_cart (session_id, customer_id, product_id, quantity)
        VALUES ('{$this->session->getId()}', {$this->customer->getId()}, {$id}, {$quantity}) AS new
        ON DUPLICATE KEY UPDATE quantity = new.quantity
    ";
    $this->db->query($sql);

    $total = $this->getCartTotal();

    header('Content-Type: application/json');
    echo json_encode([
      'isMaxQuantity'        => $quantity == $product['quantityStore'],
      'quantity'             => $quantity,
      'productTotalUSD'      => number_format($product['price'] * $quantity, 2, '.', ''),
      'productTotalUAH'      => number_format($this->main->getCurrency() * $product['price'] * $quantity, 0, '.', ''),
      'totalQuantity'        => $total['totalQuantity'],
      'totalUSD'             => $total['totalUSD'],
      'totalUAH'             => $total['totalUAH'],
      'enoughQuantityStore1' => $total['enoughQuantityStore1'],
      'enoughQuantityStore2' => $total['enoughQuantityStore2'],
      'commissionUAH'        => $total['commissionUAH']
    ]);
  }

  public function remove() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $id = (int)($requestData['id'] ?? 0);

    if (!$id) {
      http_response_code(400);
      exit('INVALID');
    }

    $sql = "
      DELETE FROM oc_cart WHERE product_id = {$id}
        AND session_id = '{$this->session->getId()}'
        AND customer_id = {$this->customer->getId()}
    ";

    $this->db->query($sql);
    header('Content-Type: application/json');
    echo json_encode($this->getCartTotal());
  }

  public function getCount() {
    $sql = "
      SELECT COALESCE(SUM(quantity), 0) AS quantity FROM oc_cart
      WHERE session_id = '{$this->session->getId()}'
        AND customer_id = {$this->customer->getId()}";
    return $this->db->query($sql)->row['quantity'];
  }

  private function getCartTotal() {
    $sql = "
      WITH
      tmpPrices AS (
        SELECT
          COALESCE(
            (SELECT price
              FROM oc_product_special
              WHERE product_id = p.product_id
                AND customer_group_id = {$this->customer->getGroupId()}
                AND (date_start = '0000-00-00' OR date_start < NOW())
                AND (date_end = '0000-00-00' OR date_end > NOW())
            ),
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = {$this->customer->getGroupId()}),
            p.price) AS price,
          c.quantity,
          p.quantity >= c.quantity AS enoughQuantityStore1,
          p.quantity_store_2 >= c.quantity AS enoughQuantityStore2,
          IF (c.quantity > p.quantity, p.quantity, c.quantity) AS quantityOrder1,
          IF (c.quantity > p.quantity,
            IF (p.quantity_store_2 > c.quantity - p.quantity, c.quantity - p.quantity, p.quantity_store_2),
            0
          ) AS quantityOrder2
        FROM oc_cart c
        LEFT JOIN oc_product p ON p.product_id = c.product_id
        WHERE c.session_id = '{$this->session->getId()}' AND c.customer_id = {$this->customer->getId()}
      )
      SELECT
        SUM(ROUND(p.price * p.quantity, 2)) AS totalUSD,
        ROUND(p.price * p.quantityOrder1, 2) AS totalOrder1USD,
        ROUND(p.price * p.quantityOrder2, 2) AS totalOrder2USD,
        SUM(ROUND(p.price * p.quantity * c.value)) AS totalUAH,
        SUM(p.quantity) AS totalQuantity,
        MIN(enoughQuantityStore1) AS enoughQuantityStore1,
        MIN(enoughQuantityStore2) AS enoughQuantityStore2
      FROM tmpPrices p
      LEFT JOIN oc_currency c ON c.currency_id = 980
    ";
    $total = $this->db->query($sql)->row;

    $total['commissionUAH'] = $this->getCommissionAll($total['totalOrder1USD'], $total['totalOrder2USD']);
    return $total;
  }

  private function getCommission($totalUSD) {
    if (!$totalUSD) return 0;
    $commissionUAH = round($totalUSD * $this->main->getCurrency() * 0.04);
    if ($commissionUAH > 10) return $commissionUAH;
    return 10;
  }

  private function getCommissionAll($totalOrder1USD, $totalOrder2USD) {
    $commissionOrder1UAH = $this->getCommission((int)$totalOrder1USD);
    $commissionOrder2UAH = $this->getCommission((int)$totalOrder2USD) ;
    return $commissionOrder1UAH + $commissionOrder2UAH;
  }
}
