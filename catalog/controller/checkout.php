<?
class ControllerCheckout extends Controller {
  public function index() {
    $data['isLogged'] = $this->customer->getId();
    $data['firstName'] = $this->customer->getFirstName();
    $data['lastName'] = $this->customer->getLastName();
    $data['phone'] = $this->customer->getPhone();
    $data['email'] = $this->customer->getEmail();
    $data['region'] = $this->customer->getRegion();
    $data['city'] = $this->customer->getCity();
    $data['warehouse'] = $this->customer->getWarehouse();

    $data['headingH1'] = 'Оформлення замовлення';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();

    $this->document->addPreload('/resourse/images/checkout-sprite-icons.svg', 'image', 'image/svg+xml');
    $this->document->addCustomStyle('/resourse/styles/checkout.min.css');
    $this->document->addPreload('/resourse/scripts/checkout.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/checkout.min.js');

    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs');
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    $data['cart'] = $this->load->controller('shared/cart/getData');
    echo $this->load->view('checkout/checkout', $data);
  }

  public function order() {
    $requestData = json_decode(file_get_contents('php://input'), true);

    $shippingFirstName = $this->db->escape($requestData['firstName'] ?? '');
    $shippingLastName = $this->db->escape($requestData['lastName'] ?? '');
    $shippingPhone = "380{$this->db->escape($requestData['phone'] ?? '')}";
    $shippingEmail = trim($this->db->escape($requestData['email'] ?? ''));
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

    if ($this->customer->getId()) {
      $firstName = $this->customer->getFirstName();
      $lastName = $this->customer->getLastName();
      $data['phone'] = $this->customer->getPhone();
    } else {
      $sql = "SELECT EXISTS (SELECT * FROM oc_customer WHERE email = '{$shippingEmail}') as `exists`";
      if ($this->db->query($sql)->row['exists']) {
        http_response_code(400);
        exit('USER_EXISTS');
      }
    }

    $sql = "
      WITH
        tmpProduct AS (
          SELECT
            c.product_id AS id,
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
            ) AS price,
            c.quantity,
            IF (c.quantity > p.quantity, p.quantity, c.quantity) AS quantityStore1,
            IF (c.quantity > p.quantity,
              IF (p.quantity_store_2 > c.quantity - p.quantity,
                c.quantity - p.quantity,
                p.quantity_store_2),
              0) AS quantityStore2
          FROM oc_cart c
          LEFT JOIN oc_product p ON p.product_id = c.product_id
          WHERE c.session_id = '{$this->session->getId()}' AND c.customer_id = {$this->customer->getId()}
        )
        SELECT
          1 AS stockId,
          SUM(price * quantityStore1) AS total,
          SUM(quantityStore1) AS quantity,
          JSON_ARRAYAGG(JSON_OBJECT('id', id, 'quantity', quantityStore1,
            'price', price, 'total', price * quantityStore1)
          ) AS products
        FROM tmpProduct
        WHERE quantityStore1 > 0
        HAVING SUM(quantityStore1)
        UNION ALL
        SELECT
          2 AS stockId,
          SUM(price * quantityStore2) AS total,
          SUM(quantityStore2) AS quantity,
          JSON_ARRAYAGG(JSON_OBJECT('id', id, 'quantity', quantityStore2,
            'price', price, 'total', price * quantityStore2)
          ) AS products
        FROM tmpProduct
        WHERE quantityStore2 > 0
        HAVING SUM(quantityStore2)
    ";

    $orderIds = [];
    $totalsUAH = 0;

    foreach ($this->db->query($sql)->rows as $order) {
      $commissionUAH = 0;
      if ($paymentMethod == "Наложеный платеж") {
        $commissionUAH = round($order['total'] * 0.04 * $this->main->getCurrency());
        if ($commissionUAH < 10) $commissionUAH = 10;
      }

      $totalsUAH += round($order['total'] * $this->main->getCurrency());

      $sql = "
        INSERT INTO oc_order (
          customer_id, customer_group_id, firstname, lastname,
          email, telephone, payment_method, shipping_firstname,
          shipping_lastname, shipping_telephone, shipping_address_1,
          shipping_method, comment, total, commission_uah,
          stock_id, date_added, date_modified,
          region, city, warehouse, quantity
        ) VALUES (
          {$this->customer->getId()}, {$this->customer->getGroupId()}, '{$firstName}', '{$lastName}',
          '{$shippingEmail}', '{$phone}', '{$paymentMethod}', '{$shippingFirstName}',
          '{$shippingLastName}', '{$shippingPhone}', '{$shippingAddress}',
          '{$shippingMethod}', '{$comment}', {$order['total']}, {$commissionUAH},
          {$order['stockId']}, NOW(), NOW(),
          '{$region}', '{$city}', '{$warehouse}', {$order['quantity']}
        )
      ";

      // file_put_contents('./catalog/controller/startup/__LOG__.txt', $sql);

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

    if ($isValidPhone && $isValidEmail) {
      $sql = "DELETE FROM oc_cart WHERE session_id = '{$this->session->getId()}' AND customer_id = {$this->customer->getId()}";
      $this->db->query($sql);
    }

    if ($this->customer->getId() && $warehouse) {
      $sql = "
        UPDATE oc_customer SET
          region = '{$region}',
          city = '{$city}',
          warehouse = '{$warehouse}',
          updated = 1
        WHERE customer_id = {$this->customer->getId()} AND warehouse = ''
      ";
      $this->db->query($sql);
    }

    if (count($orderIds)) {
      $linkSuccess = $this->url->link('checkout_success', ['orders' => $orderIds]);
      if ($paymentMethod == 'Оплата карткою онлайн (LiqPay)') {
        header('Content-Type: application/json');
        echo json_encode($this->getLiqpayData($totalsUAH, $orderIds, $linkSuccess));
        exit();
      } else {
        echo $linkSuccess;
        exit();
      }
    } else {
      http_response_code(400);
      exit('NOT_ENOUGH_QUANTITY');
    }
  }

  public function liqpayResponse() {
    $request = $this->request->post;
    $reqData = $request['data'] ?? '';
    $reqSignature = $request['signature'] ?? '';

    if ($reqSignature !== $this->getSignature($reqData)) return;
    $data = json_decode(base64_decode($reqData), true);

    if ($data['status'] !== 'success') return;
    $sql = "UPDATE oc_order SET hasPayment = 1 WHERE order_id IN ({$data['order_id']})";
    $this->db->query($sql)->row;
  }


  private function getLiqpayData($totalsUAH, $orderIds, $linkSuccess) {
    $data = base64_encode(json_encode([
      'public_key'  => LIQPAY_PUBLIC_KEY,
      'action'      => 'hold',
      'language'    => 'ru',
      'amount'      => $totalsUAH,
      'currency'    => 'UAH',
      'description' => "Оплата за товар по замовленню № " . implode(', ', $orderIds),
      'order_id'    => implode(',', $orderIds),
      'version'     => 3,
      'result_url'  => $linkSuccess,
      'server_url'  => $this->url->link('checkout/liqpayResponse')
    ]));

    return [
      'data'      => $data,
      'signature' => $this->getSignature($data)
    ];
  }

  private function getSignature($data) {
    return base64_encode(sha1(LIQPAY_PRIVATE_KEY . $data . LIQPAY_PRIVATE_KEY, 1));
  }
}

