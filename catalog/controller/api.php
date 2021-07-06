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



  // public function register() {
  //   $email = 'b360124@gmail.com';
  //   // $email = 'pavlenkoillai@gmail.com';

  //   $subject = 'UkrMobil - Дякуємо за реєстрацію';
  //   $this->mail->send($email, $subject, 'register');
  //   exit();
  // }


  public function recoveryEmail() {
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
          'link'     => $this->url->link('product', ['product_id' => $product['id']]),
          'image'    => $this->image->resize($product['image'], 60, 60)
        ];
      }

      $this->mail->send($email, 'UkrMobil - Останні надходження', 'income', ['products' => $products]);
      // $this->mail->send($email, 'UkrMobil - Останні надходження', 'vip', ['products' => $products]);
    }

    exit();
  }

  public function login() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $email = $this->db->escape(utf8_strtolower(trim($requestData['email'] ?? '')));
    $password = $this->db->escape(trim($requestData['password'] ?? ''));

    if (empty($email) || empty($password)) {
      http_response_code(401);
      exit();
    }

    $sql = "
      SELECT 1 FROM oc_customer_login
      WHERE
        email = '{$email}'
        AND TIMESTAMPDIFF(HOUR,  date_modified, now()) = 0
        AND total > 4
    ";

    $attempts = $this->db->query($sql)->row;

    if (!empty($attempts)) {
      http_response_code(400);
      echo 'ATTEMPTS';
      exit();
    }

    $sql = "
      SELECT customer_id AS id
      FROM oc_customer
      WHERE
        LOWER(email) = '{$email}'
        AND password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('{$password}')))))
        AND status = 1
    ";

    $customer = $this->db->query($sql)->row;

    if (empty($customer)) {
      http_response_code(401);
      $sql = "
        INSERT INTO oc_customer_login (email) VALUES ('{$email}')
          ON DUPLICATE KEY UPDATE
            total = total + 1,
            date_modified = NOW()
      ";
      $this->db->query($sql);
      exit();
    }

    $this->session->data['customerId'] = $customer['id'];
    $this->db->query("DELETE FROM oc_customer_login WHERE email = '{$email}'");
    exit();
  }

  public function logout() {
    unset($this->session->data['customerId']);
    exit();
  }

  public function register() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $firstName = $this->db->escape(trim($requestData['firstName'] ?? ''));
    $lastName = $this->db->escape(trim($requestData['lastName'] ?? ''));
    $phone = $this->db->escape($requestData['phone'] ?? '');
    $email = $this->db->escape(utf8_strtolower($requestData['email'] ?? ''));
    $password = $this->db->escape($requestData['password'] ?? '');
    $captcha = $requestData['captcha'] ?? '';

    $lengthFirstName = utf8_strlen($firstName);
    $lengthLastName = utf8_strlen($lastName);
    $lengthPassword = utf8_strlen($password);
    if (
      $lengthFirstName < 1 || $lengthFirstName > 32
      || $lengthLastName < 1 || $lengthLastName > 32
      || $lengthPassword < 4 || $lengthPassword > 20
      || utf8_strlen($email) > 96
      || utf8_strlen($phone) != 9
    ) {
      http_response_code(400);
      echo 'INVALID';
      exit();
    }

    if ($captcha) {
      $query = http_build_query([
        'secret'   => CAPTCHA_GOOGLE_SECRET,
        'response' => $captcha,
        'remoteip' => $_SERVER['REMOTE_ADDR']
      ]);
      $recaptcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?{$query}");
      $recaptcha = json_decode($recaptcha, true);
    }

    if (empty($recaptcha['success'])) {
      http_response_code(400);
      echo 'CAPTCHA';
      exit();
    }

    if (!empty($this->db->query("SELECT 1 FROM oc_customer WHERE email = '{$email}'")->row)) {
      http_response_code(400);
      echo 'USER_EXISTS';
      exit();
    }

    $salt = token(9);

    $sql = "
      INSERT INTO oc_customer (
        firstname, lastname, email, telephone, salt, password
      ) VALUES (
        '{$firstName}', '{$lastName}', '{$email}', '380{$phone}', '{$salt}',
        SHA1(CONCAT('{$salt}', SHA1(CONCAT('{$salt}', SHA1('{$password}')))))
      )
    ";

    $this->db->query($sql);
    $this->session->data['customerId'] = $this->db->getLastId();
    $this->mail->send($email, 'UkrMobil - Дякуємо за реєстрацію', 'register');
    echo $this->url->link('register_success');
  }

  public function recovery() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $email = $this->db->escape(utf8_strtolower(trim($requestData['email'] ?? '')));

    $sql = "SELECT customer_id AS id FROM oc_customer WHERE email = '{$email}'";
    $customer = $this->db->query($sql)->row;

    if (empty($customer)) {
      http_response_code(400);
      echo 'USER_EXISTS';
    }

    $code = token(40);
    $sql = "UPDATE oc_customer SET code = '${code}' WHERE customer_id = {$customer['id']}";
    $this->db->query($sql);

    $data['linkReset'] = $this->url->link('recovery', ['code' => $code]);
    $this->mail->send($email, 'UkrMobil - Відновлення пароля', 'recovery', $data);
    exit();
  }

  public function buy() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $phone = $this->db->escape($requestData['phone'] ?? '');
    $productId = (int)($requestData['productId'] ?? 0);

    if (!$productId || utf8_strlen($phone) != 9) {
      http_response_code(400);
      echo 'INVALID';
      exit();
    }

    $data['phone'] = '+380' . $phone;

    $sql = "
      SELECT
        p.product_id AS id,
        pd.name,
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
      INNER JOIN oc_product_description pd ON pd.product_id = p.product_id
      WHERE p.product_id = {$productId}
    ";

    $data['product'] = $this->db->query($sql)->row;

    if ($this->customer->getId()) {
      $data['customer']['fullName'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
      $data['customer']['phone'] = '+380' . $this->customer->getPhone();
      $data['customer']['email'] = $this->customer->getEmail();
    }

    $this->mail->send('ukrmobil1@gmail.com', "Купити в 1 клік {$data['phone']}", 'buy', $data);
    exit();
  }

  public function getFavorites() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($requestData['productId'] ?? 0);

    if (!$this->customer->getId() || !$productId) {
      http_response_code(400);
      echo 'INVALID';
      exit();
    }

    $sql = "
      SELECT f.id, f.name, IF(fp.favorite_id IS NULL, false, true) AS isInsert
      FROM favorite f
      LEFT JOIN favorite_product fp ON fp.favorite_id = f.id AND product_id = {$productId}
      WHERE customer_id = {$this->customer->getId()}
    ";
    header('Content-Type: application/json');
    echo json_encode($this->db->query($sql)->rows);
  }

  public function addFavoriteProduct() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($requestData['productId'] ?? 0);
    $favoriteId = (int)($requestData['favoriteId'] ?? 0);

    if (!$this->customer->getId() || !$favoriteId || !$productId) {
      http_response_code(400);
      echo 'INVALID';
      exit();
    }

    $sql = "
      INSERT INTO favorite_product (favorite_id, product_id)
        SELECT id, {$productId} FROM favorite
        WHERE id = {$favoriteId} AND customer_id = {$this->customer->getId()}
      ON DUPLICATE KEY UPDATE favorite_id = favorite_id
    ";

    $this->db->query($sql);
    exit();
  }

  public function removeFavoriteProduct() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($requestData['productId'] ?? 0);
    $favoriteId = (int)($requestData['favoriteId'] ?? 0);

    if (!$this->customer->getId() || !$favoriteId || !$productId) {
      http_response_code(400);
      echo 'INVALID';
      exit();
    }

    $sql = "
      DELETE FROM favorite_product
      WHERE product_id = {$productId}
        AND favorite_id = (SELECT id FROM favorite WHERE id = {$favoriteId} AND customer_id = {$this->customer->getId()})
    ";
    $this->db->query($sql);
    exit();
  }

  public function wishlist() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($requestData['productId'] ?? 0);

    if (!$this->customer->getId() || !$productId) {
      http_response_code(400);
      echo 'INVALID';
      exit();
    }

    $sql = "
      INSERT INTO oc_customer_wishlist (customer_id, product_id)
        VALUES ({$this->customer->getId()}, {$productId})
        ON DUPLICATE KEY UPDATE date_added = NOW()
    ";

    $this->db->query($sql);
    exit();
  }
}
