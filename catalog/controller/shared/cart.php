<?
class ControllerSharedCart extends Controller {
  public function index() {
    $sql = "
      WITH
        tmpProducts AS (
          SELECT
            c.product_id AS id,
            pd.name,
            p.quantity + p.quantity_store_2 AS productQuantity,
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
          p.productQuantity,
          p.image,
          p.price = p.priceOld AS isPromotions,
          ROUND(p.price * p.quantity, 2) AS totalUSD,
          ROUND(p.price * p.quantity * c.value) AS totalUAH,
          p.price AS priceUSD,
          ROUND(p.price * c.value) AS priceUAH,
          ROUND(p.priceOld * c.value) AS priceOldUAH
        FROM tmpProductsFull p
        LEFT JOIN oc_currency c ON c.currency_id = 980
        ORDER BY p.name
    ";

    $data['products'] = $this->db->query($sql)->rows;
    foreach ($data['products'] as &$product) {
      $data['totalUSD'] += $product['totalUSD'];
      $data['totalUAH'] += $product['totalUAH'];
      $data['quantity'] += $product['quantity'];
      $product['image'] = $this->image->resize($product['image'], 90, 110);
      $product['link'] = $this->url->link('product', ['product_id' => $product['id']]);
    }

    $data['totalUSD'] = number_format($data['totalUSD'], 2);
    $data['linkCheckout'] = $this->url->link('checkout');
    return $this->load->view('shared/cart/cart', $data);
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

    $sessionId = $this->session->getId();
    $customerId = $this->customer->getId();

    if (!$id) {
      http_response_code(400);
      exit('INVALID');
    }

    $sql = "
      SELECT
        pd.name,
        p.quantity + p.quantity_store_2 AS quantity,
        COALESCE(c.quantity, 0) AS cartQuantity
      FROM oc_product p
      LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
      LEFT JOIN oc_cart c ON c.product_id = p.product_id
        AND c.session_id = '{$this->session->getId()}'
        AND c.customer_id = {$this->customer->getId()}
      WHERE p.product_id = {$id}";

    $product = $this->db->query($sql)->row;

    if (!$product) {
      http_response_code(400);
      exit('NOT_EXISTS');
    }

    $cartQuantity = $product['cartQuantity'] + $quantity;
    if ($cartQuantity > $product['quantity']) {
      http_response_code(400);
      exit('MAX_QUANTITY');
    }

    if ($cartQuantity < 1) {
      http_response_code(400);
      exit('MIN_QUANTITY');
    }

    $sql = "
      INSERT INTO oc_cart (session_id, customer_id, product_id, quantity)
        VALUES ('{$sessionId}', {$customerId}, {$id}, {$cartQuantity}) AS new
        ON DUPLICATE KEY UPDATE quantity = new.quantity
    ";
    $this->db->query($sql);

    $total = $this->getCartTotal();

    header('Content-Type: application/json');
    echo json_encode([
      'isMaxQuantity' => $cartQuantity == $product['quantity'],
      'quantity'      => $cartQuantity,
      'totalQuantity' => $total['quantity'],
      'totalUSD'      => $total['totalUSD'],
      'totalUAH'      => $total['totalUAH']
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
          c.quantity
        FROM oc_cart c
        LEFT JOIN oc_product p ON p.product_id = c.product_id
        WHERE c.session_id = '{$this->session->getId()}'
      )
      SELECT
        SUM(ROUND(p.price * p.quantity, 2)) AS totalUSD,
        SUM(ROUND(p.price * p.quantity * c.value)) AS totalUAH,
        SUM(quantity) AS quantity
      FROM tmpPrices p
      LEFT JOIN oc_currency c ON c.currency_id = 980
    ";

    return $this->db->query($sql)->row;
  }
}
