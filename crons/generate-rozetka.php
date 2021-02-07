<?
require_once(__DIR__ . '/../config.php');
require_once(DIR_SYSTEM . 'startup.php');
require_once(DIR_APPLICATION . 'controller/startup/seo_pro.php');
require_once(DIR_APPLICATION . 'model/tool/image.php');

$registry = new Registry();

$config = new Config();
$config->load('catalog');

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$url = new Url(HTTPS_SERVER, HTTPS_SERVER);

$registry->set('db', $db);
$registry->set('cache', new Cache($config->get('cache_engine'), $config->get('cache_expire')));
$url->addRewrite(new ControllerStartupSeoPro($registry));
$modelImage = new ModelToolImage();

$sql = "
  WITH
  tmpProducts AS (
    SELECT
      p.product_id AS id,
      LEFT(pd.name, 255) AS name,
      ptc.category_id AS categoryId,
      b.name AS brand,
      b.rzValueId AS brandValueId,
      b.country,
      p.quantity + p.quantity_store_2 AS quantity,
      IF(p.quantity || p.quantity_store_2, 'true', 'false') AS available,
      ROUND(ROUND(p.price * c.value) * (100 + tmpRzMarkup.markup) / 100) AS price,
      ROUND(ROUND(
        ROUND(p.price * c.value) * (100 + tmpRzMarkup.markup) / 100) * 1.15) AS priceOld,
      tmpQuality.quality,
      tmpQuality.qualityValueId,
      tmpColor.color,
      tmpColor.colorValueId,
      tmpRzId.rzId,
      CASE
        WHEN p.image = '' AND NOT JSON_LENGTH(tmpImages.images) THEN JSON_ARRAY('placeholder.png')
        WHEN p.image != '' THEN JSON_ARRAY_INSERT(tmpImages.images, '$[0]', p.image)
        ELSE tmpImages.images
      END AS images
    FROM oc_product p
    LEFT JOIN oc_currency c ON c.currency_id = 980
    LEFT JOIN oc_product_to_category ptc ON ptc.product_id = p.product_id
    LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
    LEFT JOIN products_models pm ON pm.product_id = p.product_id
    LEFT JOIN models m ON m.id = pm.model_id
    LEFT JOIN brands b ON b.id = m.brand_id
    LEFT JOIN LATERAL (
      SELECT
        IF(COUNT(image), JSON_ARRAYAGG(image), JSON_ARRAY()) AS images
      FROM oc_product_image
      WHERE product_id = p.product_id
      ORDER BY sort_order
    ) AS tmpImages ON true
    LEFT JOIN LATERAL (
      SELECT ppv.name AS quality, ppv.rzValueId AS qualityValueId
      FROM products_properties pp
      LEFT JOIN product_property_values ppv ON ppv.id = pp.product_property_value_id
      WHERE pp.product_id = p.product_id
        AND ppv.product_property_id = (SELECT id FROM product_properties WHERE LOWER(name) = 'качество')
        AND pp.active = 1
    ) AS tmpQuality ON true
    LEFT JOIN LATERAL (
      SELECT ppv.name AS color, ppv.rzValueId AS colorValueId
      FROM products_properties pp
      LEFT JOIN product_property_values ppv ON ppv.id = pp.product_property_value_id
      WHERE pp.product_id = p.product_id
        AND ppv.product_property_id = (SELECT id FROM product_properties WHERE LOWER(name) = 'цвет')
        AND pp.active = 1
    ) AS tmpColor ON true
    LEFT JOIN LATERAL (
      SELECT c.rzId
      FROM oc_category_path cp
      LEFT JOIN oc_category c ON c.category_id = cp.path_id
      WHERE cp.category_id = ptc.category_id AND (c.rzId OR cp.level = 0)
      ORDER BY cp.level DESC
      LIMIT 1
    ) AS tmpRzId ON true
    LEFT JOIN LATERAL (
      SELECT c.rzMarkup AS markup
      FROM oc_category_path cp
      LEFT JOIN oc_category c ON c.category_id = cp.path_id
      WHERE cp.category_id = ptc.category_id AND (c.rzMarkup != 0 OR cp.level = 0)
      ORDER BY cp.level DESC
      LIMIT 1
    ) AS tmpRzMarkup ON true
    WHERE p.status AND NOT p.rzDisabled
    GROUP BY p.product_id
    ORDER BY p.product_id
  ),
  tmpCategories AS (
    SELECT categoryId, rzId FROM tmpProducts GROUP BY categoryId
  ),
  tmpCategoriesAgg AS (
    SELECT
      JSON_ARRAYAGG(
        JSON_OBJECT('id', tc.categoryId, 'rzId', tc.rzId, 'name', cd.name)
      ) AS categories
    FROM tmpCategories tc
    LEFT JOIN oc_category_description cd ON cd.category_id = tc.categoryId
  ),
  tmpProductAgg AS (
    SELECT
      JSON_ARRAYAGG(JSON_OBJECT(
        'id', id, 'quantity', quantity, 'available', available,
        'price', price, 'priceOld', priceOld, 'categoryId', categoryId,
        'images', images, 'name', name, 'brand', brand,
        'brandValueId', brandValueId, 'country', country,
        'quality', quality, 'qualityValueId', qualityValueId,
        'color', color, 'colorValueId', colorValueId
      )) AS products
    FROM tmpProducts
  )
<<<<<<< HEAD
  SELECT
    COALESCE(tmpProductAgg.products, JSON_ARRAY()) AS products ,
    COALESCE(tmpCategoriesAgg.categories, JSON_ARRAY()) AS categories
  FROM tmpProductAgg, tmpCategoriesAgg
=======
  SELECT tmpProductAgg.products, tmpCategoriesAgg.categories FROM tmpProductAgg
  LEFT JOIN tmpCategoriesAgg ON true
>>>>>>> e7440c1a704131994ebeea1a62387cf1c2eba0d3
";

$products = $db->query($sql)->row;

$content = '<?xml version="1.0" encoding="UTF-8"?>';
$content .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">';
$content .= '<yml_catalog date="' . date("Y-m-d H:i") . '">';
$content .= '<shop>';
$content .= '<name>uMobile</name>';
$content .= '<currencies><currency id="UAH" rate="1"/></currencies>';
$content .= '<categories>';
foreach (json_decode($products['categories'], true) as $category) {
  $content .= "<category id=\"{$category['id']}\""
    . " rz_id=\"{$category['rzId']}\">{$category['name']}</category>";
}
$content .= '</categories>';
$content .= '<offers>';

foreach (json_decode($products['products'], true) as $product) {
  $quantity = $product['quantity'];
  $available = $quantity ? 'true' : 'false';
  $content .= "<offer id=\"{$product['id']}\" available=\"{$product['available']}\">";
  $content .= "<price>{$product['price']}</price>";
  $content .= "<price_old>{$product['priceOld']}</price_old>";
  $content .= "<stock_quantity>{$product['quantity']}</stock_quantity>";
  $content .= "<currencyId>UAH</currencyId>";
  $content .= "<categoryId>{$product['categoryId']}</categoryId>";

  foreach ($product['images'] as $image) $content .= "<picture>"
<<<<<<< HEAD
    . "{$modelImage->resize($image)}</picture>";
=======
   . "{$modelImage->resize($image)}</picture>";
>>>>>>> e7440c1a704131994ebeea1a62387cf1c2eba0d3

  $content .= "<name>{$product['name']}</name>";
  $content .= "<vendor>{$product['brand']}</vendor>";
  $content .= "<description>{$product['name']}</description>";

  $qualityValueId = 1797382;
  $quality = 'Original (PRC)';
  if ($product['qualityValueId']) {
    $qualityValueId = $product['qualityValueId'];
    $quality = $product['quality'];
  }
  $content .= "<param name=\"Класс качества\" paramid=\"180700\" "
   . " valueid=\"{$qualityValueId}\">{$quality}</param>";

  if ($product['brandValueId']) $content .= "<param name=\"Цвет\""
    . " paramid=\"170582\" valueid=\"{$product['colorValueId']}\">{$product['color']}</param>";

  if ($product['brandValueId']) $content .= "<param name=\"Страна регистрации бренда\""
    . " paramid=\"87790\" valueid=\"{$product['brandValueId']}\">{$product['country']}</param>";

  $content .= '<param name="Гарантия" paramid="20769" valueid="6179">1 месяц</param>';
  $content .= '<param name="Доставка/Оплата" paramid="2019">Доставка: Новая Почта Оплата: Наложенный платеж, Оплата на карту, Безналичная оплата</param>';
  $content .= '</offer>';
}

$content .= '</offers>';
$content .= '</shop>';
$content .= '</yml_catalog>';

file_put_contents(__DIR__ . '/../rozetka.xml', $content);

$db = null;
