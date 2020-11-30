<?
class ControllerCheckoutCart extends Controller {
  public function index() {
    $sessionId = $this->db->escape($this->session->getId());
    $customerId = (int)$this->customer->getId();
    $sql = "
      SELECT COALESCE(SUM(quantity), 0) AS quantity FROM oc_cart
      WHERE session_id = '{$sessionId}' AND customer_id = {$customerId}";

    if (!$this->db->query($sql)->row['quantity']) {
      $this->response->redirect($this->url->link('checkout/empty_cart'));
    }

    $data['headingH1'] = 'Оформление заказа';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $data['customer'] = $this->getCustomer();
    $data['firstName'] = $this->customer->getFirstName();
    $data['lastName'] = $this->customer->getLastName();
    $data['phone'] = $this->customer->getTelephone();
    $data['email'] = $this->customer->getEmail();
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('checkout/cart', $data));
  }

  public function products() {
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
            IF (c.quantity > p.quantity, p.quantity, c.quantity) AS quantityStore1,
            IF (c.quantity > p.quantity,
              IF (p.quantity_store_2 > c.quantity - p.quantity,
                c.quantity - p.quantity,
                p.quantity_store_2),
              0) AS quantityStore2,
            p.quantity >= c.quantity AS enoughQuantityStore1,
            p.quantity_store_2 >= c.quantity AS enoughQuantityStore2,
            IF(p.image = '',
              COALESCE(
                (SELECT image FROM oc_product_image
                  WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
                'placeholder.png'
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
          p.quantityStore1,
          p.quantityStore2,
          p.quantityStore1 + p.quantityStore2 AS quantity,
          p.enoughQuantityStore1,
          p.enoughQuantityStore2,
          p.image,
          ROUND(p.price, 2) AS priceUSD,
          ROUND(p.price * c.value) AS priceUAH,
          ROUND(p.price * (p.quantityStore1 + p.quantityStore2), 2) AS totalUSD,
          ROUND(p.price * c.value * (p.quantityStore1 + p.quantityStore2)) AS totalUAH,
          ROUND(p.price * p.quantityStore1, 2) AS totalStore1USD,
          ROUND(p.price * p.quantityStore2, 2) AS totalStore2USD
        FROM tmpProduct p
        LEFT JOIN oc_currency c ON c.currency_id = 980
        ORDER BY p.name
    ";

    $products = $this->db->query($sql)->rows;

    $totalUSD = 0;
    $totalStore1USD = 0;
    $totalStore2USD = 0;
    $quantityStore1 = 0;
    $quantityStore2 = 0;
    $enoughQuantityStore1 = true;
    $enoughQuantityStore2 = true;

    foreach ($products as &$product) {
      $totalUSD += $product['totalUSD'];
      $totalStore1USD += $product['totalStore1USD'];
      $totalStore2USD += $product['totalStore2USD'];
      $quantityStore1 += $product['quantityStore1'];
      $quantityStore2 += $product['quantityStore2'];
      if (!$product['enoughQuantityStore1']) $enoughQuantityStore1 = false;
      if (!$product['enoughQuantityStore2']) $enoughQuantityStore2 = false;
      $product['image'] = $this->model_tool_image->resize($product['image'], 36, 36);
      $product['link'] = $this->url->link('product/product', ['product_id' => $product['product_id']]);
    }

    $currency = $this->getCurrency();

    $commissionStore1UAH = 0;
    if ($quantityStore1) {
      $commissionStore1UAH = round($totalStore1USD * $currency * 0.04);
      if ($commissionStore1UAH < 10) $commissionStore1UAH = 10;
    }

    $commissionStore2UAH = 0;
    if ($quantityStore2) {
      $commissionStore2UAH = round($totalStore2USD * $currency * 0.04);
      if ($commissionStore2UAH < 10) $commissionStore2UAH = 10;
    }
    $commissionUAH = $commissionStore1UAH + $commissionStore2UAH;

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode([
      'products'             => $products,
      'totalUSD'             => number_format($totalUSD, 2),
      'totalUAH'             => round($totalUSD * $currency),
      'commissionUAH'        => $commissionUAH,
      'toPayUSD'             => number_format(round($totalUSD + $commissionUAH / $currency, 2), 2),
      'toPayUAH'             => round($totalUSD * $currency + $commissionUAH),
      'enoughQuantityStore1' => $enoughQuantityStore1,
      'enoughQuantityStore2' => $enoughQuantityStore2
    ]));
  }

  public function remove() {
    $request = json_decode(file_get_contents('php://input'), true);
    $cartId = (int)($request['cartId'] ?? 0);
    if ($cartId) $this->db->query("DELETE FROM oc_cart WHERE cart_id = {$cartId}");
    $this->response->setOutput('');
  }

  public function edit() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $cartId = (int)($requestData['cartId'] ?? 0);
    $quantity = (int)($requestData['quantity'] ?? 0);

    $sql = "
      SELECT p.quantity + p.quantity_store_2 AS quantityTotal
      FROM oc_cart c
      LEFT JOIN oc_product p ON p.product_id = c.product_id
      WHERE c.cart_id = {$cartId}";

    $quantityTotal = $this->db->query($sql)->row['quantityTotal'] ?? 0;
    if ($quantity <= $quantityTotal) {
      $this->db->query("UPDATE oc_cart SET quantity = {$quantity} WHERE cart_id = {$cartId}");
      $enoughQuantity = true;
    }
    $this->response->setOutput(isset($enoughQuantity));
  }

  public function order() {
    $requestData = json_decode(file_get_contents('php://input'), true);

    $sessionId = $this->db->escape($this->session->getId());
    $customerId = (int)$this->customer->getId();
    $customerGroupId = (int)($this->customer->getGroupId() ?? 1);

    $shippingFirstName = $this->db->escape($requestData['firstName'] ?? '');
    $shippingLastName = $this->db->escape($requestData['lastName'] ?? '');
    $shippingPhone = $this->db->escape($requestData['phone'] ?? '');
    $shippingEmail = $this->db->escape($requestData['email'] ?? '');
    $shippingMethod = $this->db->escape($requestData['shippingMethod'] ?? '');
    $shippingAddress = $this->db->escape($requestData['shippingAddress'] ?? '');
    $paymentMethod = $this->db->escape($requestData['paymentMethod'] ?? '');
    $isValidPhone = (int)($requestData['isValidPhone'] ?? 0);
    $isValidEmail = (int)($requestData['isValidEmail'] ?? 0);
    $comment = $this->db->escape($requestData['comment'] ?? '');

    $region = $this->db->escape($requestData['region'] ?? '');
    $city = $this->db->escape($requestData['city'] ?? '');
    $warehouse = $this->db->escape($requestData['warehouse'] ?? '');

    $firstName = $shippingFirstName;
    $lastName = $shippingLastName;
    $phone = $shippingPhone;

    if ($this->customer->isLogged()) {
      $firstName = $this->customer->getFirstName();
      $lastName = $this->customer->getLastName();
      $phone = $this->customer->getTelephone();
    }

    $sql = "
      WITH
        tmpProduct AS (
          SELECT
            c.product_id,
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
            IF (c.quantity > p.quantity, p.quantity, c.quantity) AS quantityStore1,
            IF (c.quantity > p.quantity,
              IF (p.quantity_store_2 > c.quantity - p.quantity,
                c.quantity - p.quantity,
                p.quantity_store_2),
              0) AS quantityStore2
          FROM oc_cart c
          LEFT JOIN oc_product p ON p.product_id = c.product_id
          LEFT JOIN oc_product_discount pdc ON pdc.product_id = p.product_id
            AND pdc.customer_group_id = {$customerGroupId}
          WHERE c.session_id = '{$sessionId}' AND c.customer_id = {$customerId}
        )
        SELECT
          1 AS stock_id,
          SUM(price * quantityStore1) AS total,
          JSON_ARRAYAGG(JSON_OBJECT('id', product_id, 'quantity', quantityStore1,
            'price', price, 'total', price * quantityStore1)
          ) as products
        FROM tmpProduct
        WHERE quantityStore1 > 0
        HAVING SUM(quantityStore1)
        UNION ALL
        SELECT
          2 AS stock_id,
          SUM(price * quantityStore2) AS total,
          JSON_ARRAYAGG(JSON_OBJECT('id', product_id, 'quantity', quantityStore2,
            'price', price, 'total', price * quantityStore2)
          ) as products
        FROM tmpProduct
        WHERE quantityStore2 > 0
        HAVING SUM(quantityStore2)
    ";

    $orderIds = [];
    $currency = $this->getCurrency();

    foreach ($this->db->query($sql)->rows as $order) {
      $commissionUAH = 0;
      if ($paymentMethod == "Наложеный платеж") {
        $commissionUAH = round($order['total'] * 0.04 * $currency, 0);
        if ($commissionUAH < 10) $commissionUAH = 10;
      }

      $sql = "
        INSERT INTO oc_order (
          customer_id, customer_group_id, firstname, lastname,
          email, telephone, payment_method, shipping_firstname,
          shipping_lastname, shipping_telephone, shipping_address_1,
          shipping_method, comment, total, commission_uah,
          stock_id, date_added, date_modified
        ) VALUES (
          {$customerId}, {$customerGroupId}, '{$firstName}', '{$lastName}',
          '{$shippingEmail}', '{$phone}', '{$paymentMethod}', '{$shippingFirstName}',
          '{$shippingLastName}', '{$shippingPhone}', '{$shippingAddress}',
          '{$shippingMethod}', '{$comment}', {$order['total']}, {$commissionUAH},
          {$order['stock_id']}, NOW(), NOW()
        )
      ";

      $this->db->query($sql);
      $orderId = $this->db->getLastId();
      $orderIds[] = $orderId;

      foreach (json_decode($order['products'], true) as $product) {
        $sql = "
          INSERT INTO oc_order_product (
            order_id, product_id, quantity, price, total
          ) VALUES (
            {$orderId}, {$product['id']}, {$product['quantity']},
            {$product['price']}, {$product['total']}
          )
        ";
        $this->db->query($sql);
      }
    }

    $sql = "DELETE FROM oc_cart WHERE session_id = '{$sessionId}' AND customer_id = {$customerId}";
    $this->db->query($sql);

    if ($customerId && $warehouse) {
      $sql = "
        UPDATE oc_customer SET
          region = '{$region}',
          city = '{$city}',
          warehouse = '{$warehouse}',
          updated = 1
        WHERE customer_id = {$customerId} AND warehouse = ''
      ";
      $this->db->query($sql);
    }

    $this->response->setOutput($this->url->link('checkout/success', ['orders' => $orderIds]));
  }

  private function getCurrency() {
    $sql = "SELECT value FROM oc_currency WHERE currency_id = 980";
    return $this->db->query($sql)->row['value'] ?? 0;
  }

  private function getCustomer() {
    $customerId = (int)$this->customer->getId();
    $sql = "
      SELECT
        firstname AS firstName,
        lastname AS lastName,
        email,
        telephone AS phone,
        region,
        city,
        warehouse
      FROM oc_customer where customer_id = {$customerId}
    ";
    return $this->db->query($sql)->row;
  }
}
