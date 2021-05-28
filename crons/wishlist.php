<?
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../ego/src/autoload.php');
require_once(DIR_SYSTEM . 'startup.php');
require_once(DIR_APPLICATION . 'controller/startup/seo_pro.php');

$registry = new Registry();

$config = new Config();
$config->load('catalog');

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$url = new Url(HTTPS_SERVER, HTTPS_SERVER);
$image = new Image();
$mail = new Mail();

$registry->set('db', $db);
$registry->set('cache', new Cache($config->get('cache_engine'), $config->get('cache_expire')));
$url->addRewrite(new ControllerStartupSeoPro($registry));

$sql = "
  WITH
  tmpProducts AS (
    SELECT
      cw.customer_id AS customerId,
      cw.product_id AS productId,
      c.email,
      pd.name,
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
    FROM oc_customer_wishlist cw
    LEFT JOIN oc_customer c ON c.customer_id = cw.customer_id
    LEFT JOIN oc_product p ON p.product_id = cw.product_id
    LEFT JOIN oc_product_description pd ON pd.product_id = cw.product_id
    WHERE p.quantity OR p.quantity_store_2
  )
  SELECT
    p.customerId,
    p.email,
    JSON_ARRAYAGG(JSON_OBJECT(
      'id', p.productId, 'name', p.name, 'priceUSD', p.price, 'priceUAH', ROUND(p.price * c.value),
      'image', p.image
    )) AS products FROM tmpProducts p
  LEFT JOIN oc_currency c ON c.currency_id = 980
  GROUP BY customerId
";

foreach ($db->query($sql)->rows as $item) {
  $customerId = $item['customerId'];
  $email = $item['email'];

  $products = [];
  $productIds = [];
  foreach (json_decode($item['products'], true) as $product) {
    $productIds[] = $product['id'];
    if (!$product['name']) continue;
    $products[] = [
      'id'       => $product['id'],
      'name'     => $product['name'],
      'priceUSD' => $product['priceUSD'],
      'priceUAH' => $product['priceUAH'],
      'link'     => $url->link('product/product', ['product_id' => $product['id']]),
      'image'    => $image->resize($product['image'], 60, 60)
    ];
  }

  if ($email && count($products)) {
    try {
      echo $email . "\n";
      $subject = 'UkrMobil - Товар в наявності!';
      $mail->send($email, $subject, 'wishlist', ['products' => $products]);
    } catch (\Exception $ex) {
      echo $ex->getMessage() . '<br>';
    }
  }

  $productIdsSQL = implode(',', $productIds);
  $sql = "DELETE FROM oc_customer_wishlist WHERE customer_id = {$customerId} AND product_id IN ({$productIdsSQL})";
  // $db->query($sql);
}

$db = null;
