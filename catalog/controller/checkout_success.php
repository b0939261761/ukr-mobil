<?
class ControllerCheckoutSuccess extends Controller {
  public function index() {
    $orderIds = (array)($this->request->get['orders'] ?? []);
    $orderIdsSQL = $this->db->escape(implode(', ', $orderIds));
    $data['order'] = $this->getOrder($orderIdsSQL);
    // if (!empty($data['order']['sent'])) {
    //   header("Location: {$this->url->link('home/home')}");
    //   exit();
    // }

    $data['products'] = $this->getOrderProducts($orderIdsSQL);
    $this->db->query("UPDATE oc_order SET sent = 1 WHERE order_id IN ({$orderIdsSQL})");
    $this->sendEmail($data['order'], $data['products']);

    $data['headingH1'] = 'Замовлення сформованно';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $this->document->setDataLayer($this->getDataLayer($orderIds, $data['order'], $data['products']));

    $this->document->addPreload('/resourse/images/checkout-success-sprite-icons.svg', 'image', 'image/svg+xml');
    $this->document->addCustomStyle('/resourse/styles/checkout-success.min.css');

    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs');
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('checkout_success/checkout_success', $data);
  }

  private function getOrder($orderIdsSQL) {
    if (empty($orderIdsSQL)) return null;
    $sql = "
      SELECT
        CONCAT(o.shipping_firstname, ' ', o.shipping_lastname) as shippingFullname,
        o.email,
        DATE_FORMAT(o.date_added, '%d.%m.%Y %H:%i') AS date,
        o.telephone AS customerPhone,
        o.shipping_telephone AS shippingPhone,
        o.shipping_method AS shippingMethod,
        o.shipping_address_1 AS shippingAddress,
        o.payment_method AS paymentMethod,
        SUM(o.quantity) AS quantity,
        SUM(o.total) AS totalUSD,
        ROUND(SUM(o.total) * c.value) AS totalUAH,
        SUM(o.commission_uah) AS commissionUAH,
        ROUND(SUM(o.total) * c.value + SUM(o.commission_uah)) AS toPayUAH,
        o.sent
      FROM oc_order o
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE o.order_id IN ({$orderIdsSQL})
    ";

    $order = $this->db->query($sql)->row;
    if ($order) {
      $shippingIcons = [
        'Новая Почта'                       => 'new-post',
        'Самовывоз из г. Черновцы'          => 'car',
        'Самовывоз из г. Ровно'             => 'car',
        'Курьерская доставка "Новая почта"' => 'courier-new-post',
        'Курьерская доставка г. Ровно'      => 'courier',
        'Укрпочта'                          => 'ukrpost',
        'Justin'                            => 'justin'
      ];

      $paymentIcons = [
        'Наложеный платеж'                             => 'cash-delivery',
        'Оплата картой онлайн (LiqPay)'                => 'card',
        'Оплата на карту Приват Банка'                 => 'privat',
        'Безналичная оплата на счет юридического лица' => 'cash-less',
        'В долг'                                       => 'debt-pay',
        'google'                                       => 'google',
        'apple'                                        => 'apple'
      ];

      $order['orderIds'] = $orderIdsSQL;
      $order['shippingIcon'] = $shippingIcons[$order['shippingMethod']];
      $order['paymentIcon'] = $paymentIcons[$order['paymentMethod']];
    }
    return $order;
  }

  private function getOrderProducts($orderIdsSQL) {
    $sql = "
      SELECT
        op.product_id AS id,
        pd.name,
        SUM(op.quantity) AS quantity,
        ROUND(op.price, 2) AS priceUSD,
        ROUND(op.price * c.value) AS priceUAH,
        ROUND(SUM(total), 2) AS totalUSD,
        ROUND(SUM(total) * c.value) AS totalUAH,
        IF(p.image = '',
          COALESCE(
            (SELECT image FROM oc_product_image
              WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
            'placeholder.jpg'
          ),
          p.image
        ) AS image
      FROM oc_order_product op
      LEFT JOIN oc_product p ON p.product_id = op.product_id
      LEFT JOIN oc_product_description pd ON pd.product_id = op.product_id
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE op.order_id IN ({$orderIdsSQL})
      GROUP BY op.product_id
      ORDER BY pd.name
    ";

    $products = $this->db->query($sql)->rows;
    foreach ($products as &$product) {
      $image = $product['image'];
      $product['link'] = $this->url->link('product', ['product_id' => $product['id']]);
      $product['image'] = $this->image->resize($image, 87, 87);
      $product['imageEmail'] = $this->image->resize($image, 60, 60);
    }
    return $products;
  }

  private function getDataLayer($orderIds, $order, $products) {
    $transactionId = implode('-', $orderIds);

    $productList = json_encode(array_map(
      function ($item) {
        return [
          'sku'      => $item['id'],
          'name'     => $item['name'],
          'price'    => $item['priceUSD'],
          'quantity' => $item['quantity']
        ];
      },
      $products
    ));

    return "
      dataLayer = [{
        transactionId: '{$transactionId}',
        transactionAffiliation: 'UKRMobil',
        transactionTotal: {$order['totalUSD']},
        transactionProducts: {$productList},
        event: 'trackTrans'
      }];
    ";
  }

  private function sendEmail($order, $products) {
    $order['email'] = 'b360124@gmail.com';
    $data = [ 'order' => $order, 'products' => $products ];
  //   $this->mail->send($order['email'], 'UkrMobil - Нове замовлення', 'order', $data);
  }
}

