<?
class ControllerApi extends Controller {
  public function product_image() {
    $imageName = 'placeholder.jpg';
    $id = (int)($this->request->get['id'] ?? 0);

    if ($id) {
      $sql = "
        SELECT
          IF(p.image = '',
            COALESCE(
              (SELECT image FROM oc_product_image
                WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
              '{$imageName}'
            ),
            p.image
          ) AS image
        FROM oc_product p WHERE p.product_id = {$id}
      ";

      $imageName = $this->db->query($sql)->row['image'] ?? $imageName;
    }

    $image = $this->image->resize($imageName, 60, 60);
    echo str_replace(HTTP_SERVER . 'image', DIR_IMAGE, $image);

    // file_get_contents(imagePath );

    // $path_url = parse_url($image, PHP_URL_PATH);
    // $path_image = __DIR__ . "/../../..{$path_url}";
    // $imagedata = file_get_contents($path_image);

    // header("Content-type: image/jpeg");
    // imagejpeg($img);
  }



  public function feedback() {
    $requestData = json_decode(file_get_contents('php://input'), true);

    $subject = 'Помилка на сайті';
    $email = 'ukrmobil1@gmail.com';
    if ($requestData['type'] == 'manager') {
      $subject = 'Лист директору';
      $email = 'director.ukrmobil@gmail.com';
    }

    $data = [
      'name'        => $requestData['name'],
      'phone'       => $requestData['phone'],
      'email'       => $requestData['email'],
      'description' => $requestData['description']
    ];

    $this->mail->send($email, $subject, 'feedback', $data);
    exit();
  }



  public function register() {
    $email = 'b360124@gmail.com';
    // $email = 'pavlenkoillai@gmail.com';

    $subject = 'UkrMobil - Дякуємо за реєстрацію';
    $this->mail->send($email, $subject, 'register');
    exit();
  }


  public function recovery() {
    $email = 'b360124@gmail.com';
    // $email = 'pavlenkoillai@gmail.com';

    $subject = 'UkrMobil - Відновлення пароля';
    $code = '11111';
    $data['linkReset'] = $this->url->link('account/reset', ['code' => $code]);
    $this->mail->send($email, $subject, 'recovery', $data);
    exit();
  }


  public function vip() {
    $email = 'b360124@gmail.com';
    $subject = 'UkrMobil - Оформлено 5 замовлень';
    $this->mail->send($email, $subject, 'vip');
    exit();
  }

  public function income() {
    $email = 'b360124@gmail.com';
    // $email = 'pavlenkoillai@gmail.com';

    $incomeNumber = '0000-000035';
    $dateIncome = '2021-04-09 00:00:00';
    $customerId = 880;

    $sql = "
      WITH
      tmpProducts AS (
        SELECT
          c.email,
          pli.product_id AS productId,
          pd.name,
          pli.quantity,
          COALESCE(
            (SELECT price
              FROM oc_product_special
              WHERE product_id = p.product_id
                AND customer_group_id = c.customer_group_id
                AND (date_start = '0000-00-00' OR date_start < NOW())
                AND (date_end = '0000-00-00' OR date_end > NOW())
            ),
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = c.customer_group_id),
            p.price
          ) AS price,
          IF(p.image = '',
            COALESCE(
              (SELECT image FROM oc_product_image
                WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
              'placeholder.jpg'
            ),
            p.image
          ) AS image
        FROM oc_customer c
        LEFT JOIN oc_product_last_income pli ON pli.income_number = '0000-000035' AND pli.date_income = '2021-04-09 00:00:00'
        INNER JOIN oc_product_description pd ON pd.product_id = pli.product_id
        INNER JOIN oc_product p ON p.product_id = pli.product_id
        WHERE c.newsletter AND email = 'b360124@gmail.com'
      )
      SELECT
        p.email,
        JSON_ARRAYAGG(JSON_OBJECT(
          'id', p.productId, 'name', p.name, 'priceUSD', p.price, 'priceUAH', ROUND(p.price * c.value),
          'image', p.image, 'quantity', p.quantity
        )) AS products
      FROM tmpProducts p
      LEFT JOIN oc_currency c ON c.currency_id = 980
      GROUP BY p.email
    ";

    foreach ($this->db->query($sql)->rows as $item) {
      $email = $item['email'];

      $products = [];

      $productsRaw = json_decode($item['products'], true);
      uasort($productsRaw, function ($a, $b) { return strnatcmp($a['name'], $b['name']); });

      foreach ($productsRaw as $product) {
        $products[] = [
          'id'       => $product['id'],
          'name'     => $product['name'],
          'quantity' => $product['quantity'],
          'priceUSD' => $product['priceUSD'],
          'priceUAH' => $product['priceUAH'],
          'link'     => $this->url->link('product/product', ['product_id' => $product['id']]),
          'image'    => $this->image->resize($product['image'], 60, 60)
        ];
      }

      $this->mail->send($email, 'UkrMobil - Останні надходження', 'income', ['products' => $products]);
      // $this->mail->send($email, 'UkrMobil - Останні надходження', 'vip', ['products' => $products]);
    }

    exit();
  }

  public function order() {
    // $email = 'b360124@gmail.com';
    $email = 'pavlenkoillai@gmail.com';


    $orders = [148175414, 148175415];
    $orderIds = $this->db->escape(implode(', ', $orders));


    $sql = "
      SELECT
        CONCAT(o.firstname, ' ', o.lastname) as customerFullname,
        CONCAT(o.shipping_firstname, ' ', o.shipping_lastname) as shippingFullname,
        o.email,
        DATE_FORMAT(o.date_added, '%d.%m.%Y %H:%i') AS date,
        o.telephone AS customerPhone,
        o.shipping_telephone AS shippingPhone,
        o.shipping_method AS shippingMethod,
        o.shipping_address_1 AS shippingAddress,
        o.payment_method AS paymentMethod,
        SUM(o.total) AS totalUSD,
        ROUND(SUM(o.total) * c.value) AS totalUAH,
        SUM(o.commission_uah) AS commissionUAH,
        ROUND(SUM(o.total) + SUM(o.commission_uah) / c.value , 2) AS toPayUSD,
        ROUND(SUM(o.total) * c.value + SUM(o.commission_uah)) AS toPayUAH,
        o.sent
      FROM oc_order o
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE o.order_id IN ({$orderIds})
    ";

    $order = $this->db->query($sql)->row;

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
      WHERE op.order_id IN ({$orderIds})
      GROUP BY op.product_id
      ORDER BY pd.name
    ";

    $products = [];

    foreach ($this->db->query($sql)->rows as $product) {
      $products[] = [
        'id'       => $product['id'],
        'name'     => $product['name'],
        'quantity' => $product['quantity'],
        'priceUSD' => $product['priceUSD'],
        'priceUAH' => $product['priceUAH'],
        'totalUSD' => $product['totalUSD'],
        'totalUAH' => $product['totalUAH'],
        'link'  => $this->url->link('product/product', ['product_id' => $product['id']]),
        'image' => $this->image->resize($product['image'], 60, 60)
      ];
    }

    $order['email'] = 'b360124@gmail.com';
    // $order['email'] = 'pavlenkoillai@gmail.com';

    $order['orderIds'] = $orderIds;
    $data = [ 'order' => $order, 'products' => $products ];

    $this->mail->send($order['email'], 'UkrMobil - Нове замовлення', 'order', $data);
    exit();
  }
}