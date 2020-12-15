<?
class ControllerCheckoutSuccess extends Controller {
  public function index() {
    $orders = $this->request->get['orders'];
    $data['orderIds'] = $this->db->escape(implode(', ', $orders));
    $data['order'] = $this->getOrder($data['orderIds']);

    if (!empty($data['order']['sent'])) $this->response->redirect($this->url->link('common/home'));

    $data['products'] = $this->getOrderProducts($data['orderIds']);
    $this->db->query("UPDATE oc_order SET sent = 1 WHERE order_id IN ({$data['orderIds']})");
    $data['order']['totalUSD'] = number_format($data['order']['totalUSD'], 2); // какой-то глюк с не форматирует число
    $data['order']['toPayUSD'] = number_format($data['order']['toPayUSD'], 2);
    $dataLayer = $this->getDataLayer($orders, $data['order']['totalUSD'], $data['products']);
    $this->document->setDataLayer($dataLayer);
    $this->sendEmail($data['orderIds'], $data['order'], $data['products']);
    $data['linkHome'] = $this->url->link('common/home');
    $data['headingH1'] = 'Спасибо за ваш заказ!';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('checkout/success', $data));
  }

  private function getOrder($sqlOrders) {
    $sql = "
      SELECT
        CONCAT(firstname, ' ', lastname) as customerFullname,
        CONCAT(shipping_firstname, ' ', shipping_lastname) as shippingFullname,
        email,
        telephone AS customerPhone,
        shipping_telephone AS shippingPhone,
        shipping_method AS shippingMethod,
        shipping_address_1 AS shippingAddress,
        payment_method AS paymentMethod,
        SUM(total) AS totalUSD,
        ROUND(SUM(total) * c.value) AS totalUAH,
        SUM(commission_uah) AS commissionUAH,
        ROUND(SUM(total) + SUM(commission_uah) / c.value , 2) AS toPayUSD,
        ROUND(SUM(total) * c.value + SUM(commission_uah)) AS toPayUAH,
        sent
      FROM oc_order
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE order_id IN ({$sqlOrders})
    ";
    return $this->db->query($sql)->row;
  }

  private function getOrderProducts($sqlOrders) {
    $sql = "
      SELECT
        op.product_id AS code,
        pd.name,
        ROUND(op.price, 2) AS priceUSD,
        ROUND(op.price * c.value) AS priceUAH,
        SUM(op.quantity) AS quantity,
        ROUND(SUM(total), 2) AS totalUSD,
        ROUND(SUM(total) * c.value) AS totalUAH,
        IF(p.image = '',
          COALESCE(
            (SELECT image FROM oc_product_image
              WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
            'placeholder.png'
          ), p.image) AS image
      FROM oc_order_product op
      LEFT JOIN oc_product p ON p.product_id = op.product_id
      LEFT JOIN oc_product_description pd ON pd.product_id = op.product_id
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE op.order_id IN ({$sqlOrders})
        AND pd.language_id = 2
      GROUP BY op.product_id
      ORDER BY pd.name
    ";

    $this->load->model('tool/image');
    $products = $this->db->query($sql)->rows;
    foreach ($products as &$product) {
      $product['image'] = $this->model_tool_image->resize($product['image'], 200, 200);
    }

    return $products;
  }

  private function getDataLayer($orders, $total, $products) {
    $transactionId = implode('-', $orders);

    $productList = json_encode(array_map(
      function ($item) {
        return [
          'sku'      => $item['code'],
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
        transactionTotal: {$total},
        transactionProducts: {$productList},
        event: 'trackTrans'
      }];
    ";
  }

  private function sendEmail($orders, $order, $products) {
    $configService = new \Ego\Services\ConfigService();
    $emails = $configService->getEmailAdministrator();
    $emails[] = $order['email'];

    (new \Ego\Providers\MailProvider())
      ->setTo($emails)
      ->setFrom($configService->getEmailAdministratorMain(), $configService->getSiteTitle())
      ->setSubject('Новый заказ')
      ->setView('mails.new-order')
      ->setBodyData([
        'header-title'     => 'Новый заказ',
        'orderIds'         => $orders,
        'customerFullname' => $order['customerFullname'],
        'shippingMethod'   => $order['shippingMethod'],
        'shippingFullname' => $order['shippingFullname'],
        'shippingPhone'    => $order['shippingPhone'],
        'shippingAddress'  => $order['shippingAddress'],
        'totalUSD'         => $order['totalUSD'],
        'totalUAH'         => $order['totalUAH'],
        'commissionUAH'    => $order['commissionUAH'],
        'toPayUSD'         => $order['toPayUSD'],
        'toPayUAH'         => $order['toPayUAH'],
        'products'         => $products,
      ])
      ->sendMail();
  }
}
