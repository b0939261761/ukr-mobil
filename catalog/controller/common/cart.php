<?
class ControllerCommonCart extends Controller {
  public function index() {
    $this->load->model('tool/image');

    $sessionId = $this->db->escape($this->session->getId());
    $customerId = (int)$this->customer->getId();
    $customerGroupId = (int)($this->customer->getGroupId() ?? 1);

    $sql = "
      WITH
        tmpProduct AS (
          SELECT
            c.cart_id,
            c.product_id,
            pd.name,
            COALESCE(
              (SELECT price
                FROM oc_product_special
                WHERE product_id = p.product_id
                  AND customer_group_id = {$customerGroupId}
                  AND (date_start = '0000-00-00' OR date_start < NOW())
                  AND (date_end = '0000-00-00' OR date_end > NOW())
                ORDER BY priority ASC, price ASC LIMIT 1),
              pdc.price,
              p.price) AS price,
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
          LEFT JOIN oc_product_discount pdc ON pdc.product_id = p.product_id
            AND pdc.customer_group_id = {$customerGroupId}
          WHERE c.session_id = '{$sessionId}' AND c.customer_id = {$customerId}
        )
        SELECT
          p.cart_id,
          p.product_id,
          p.name,
          p.quantity,
          p.image,
          ROUND(p.price * p.quantity, 2) AS totalUSD,
          ROUND(p.price * p.quantity * c.value) AS totalUAH
        FROM tmpProduct p
        LEFT JOIN oc_currency c ON c.currency_id = 980
        ORDER BY p.name
    ";

    $totalUSD = 0;
    $quantity = 0;
    $data['products'] = $this->db->query($sql)->rows;
    foreach ($data['products'] as &$product) {
      $totalUSD += $product['totalUSD'];
      $quantity += $product['quantity'];
      $product['image'] = $this->model_tool_image->resize($product['image'], 47, 47);
      $product['link'] = $this->url->link('product/product', ['product_id' => $product['product_id']]);
    }

    $sql = "SELECT value FROM oc_currency WHERE currency_id = 980";
    $currency = $this->db->query($sql)->row['value'] ?? 0;

    $data['totalUSD'] = number_format($totalUSD, 2);
    $data['totalUAH'] = round($totalUSD * $currency);
    $data['quantity'] = $quantity;
    $data['cart'] = $this->url->link('checkout/cart');
    return $this->load->view('common/cart', $data);
  }

  public function info() {
    $this->response->setOutput($this->index());
  }

  public function add() {
    $request = json_decode(file_get_contents('php://input'), true);
    $json = ['error' => 'Недостаточно товаров на складе'];

    $sessionId = $this->db->escape($this->session->getId());
    $customerId = (int)$this->customer->getId();
    $productId = (int)($this->request->post['product_id'] ?? 0);
    $quantity = (int)($this->request->post['quantity'] ?? 1);

    $sql = "
      SELECT
        pd.name,
        p.quantity + p.quantity_store_2 AS quantityTotal,
        COALESCE(c.quantity, 0) AS cartQuantity
      FROM oc_product p
      LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
      LEFT JOIN oc_cart c ON c.product_id = p.product_id
        AND c.session_id = '{$sessionId}' AND c.customer_id = {$customerId}
      WHERE p.product_id = {$productId}";

    $product = $this->db->query($sql)->row;

    if ($product) {
      $cartQuantity = $product['cartQuantity'] + $quantity;
      if ($cartQuantity <= $product['quantityTotal']) {
        $sql = "
          INSERT INTO oc_cart (session_id, customer_id, product_id, quantity)
            VALUES ('{$sessionId}', {$customerId}, {$productId}, {$cartQuantity}) AS new
            ON DUPLICATE KEY UPDATE quantity = new.quantity
        ";
        $this->db->query($sql);

        $success = "
          Успех: вы добавили
          <a href=\"{$this->url->link('product/product', ['product_id' => $productId])}\">
            {$product['name']}
          </a> в вашу <a href=\"{$this->url->link('checkout/cart')}\">корзину</a>!";

        $json = [
          'success' => $success,
          'total'   => $this->getCountProducts()
        ];
      }
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function remove() {
    $request = json_decode(file_get_contents('php://input'), true);
    $cartId = (int)($request['cartId'] ?? 0);
    $this->db->query("DELETE FROM oc_cart WHERE cart_id = {$cartId}");
    $this->response->setOutput($this->getCountProducts());
  }

  private function getCountProducts() {
    $sessionId = $this->db->escape($this->session->getId());
    $customerId = (int)$this->customer->getId();
    $sql = "
      SELECT COALESCE(SUM(quantity), 0) AS quantity FROM oc_cart
      WHERE session_id = '{$sessionId}' AND customer_id = {$customerId}";
    return $this->db->query($sql)->row['quantity'];
  }
}
