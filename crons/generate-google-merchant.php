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

$content = $sitemapImage = '<?xml version="1.0" encoding="UTF-8"?>';
$content .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">';
$content .= '<channel>';
$content .= '<title>Запчасти и оборудование для ремонта мобильных телефонов, планшетов, смарт-часов в Черновцах, Ровно, Украине в интернет-магазине UkrMobil</title>';
$content .= "<link>{$url->link('common/home')}</link>";
$content .= '<description>Запчасти и оборудование для ремонта мобильных телефонов, планшетов, смарт-часов в Черновцах, Ровно, Украине в интернет-магазине UkrMobil</description>';

$sql = "
  SELECT
    p.product_id AS id,
    pd.name,
    b.name AS brand,
    IF(p.quantity OR p.quantity_store_2, 'in_stock', 'out_of_stock') AS availability,
    ROUND(p.price * c.value) AS price,
    p.gmDescription,
    p.gmCategory,
    tmpColor.color,
    tmpCategory.category,
    CASE
      WHEN p.image = '' AND NOT JSON_LENGTH(tmpImages.images) THEN JSON_ARRAY('placeholder.png')
      WHEN p.image != '' THEN JSON_ARRAY_INSERT(tmpImages.images, '$[0]', p.image)
      ELSE tmpImages.images
    END AS images
  FROM oc_product p
  LEFT JOIN oc_currency c ON c.currency_id = 980
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
    SELECT ppv.name AS color
    FROM products_properties pp
    LEFT JOIN product_property_values ppv ON ppv.id = pp.product_property_value_id
    WHERE pp.product_id = p.product_id
      AND ppv.product_property_id = (SELECT id FROM product_properties WHERE LOWER(name) = 'цвет')
      AND pp.active = 1
  ) AS tmpColor ON true
  LEFT JOIN LATERAL (
    SELECT GROUP_CONCAT(cd.name ORDER by cp.level SEPARATOR ' &gt; ') AS category
    FROM oc_product_to_category ptc
    LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
    LEFT JOIN oc_category c ON c.category_id = cp.path_id
    LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
    WHERE ptc.product_id = p.product_id AND c.status = 1
  ) AS tmpCategory ON true
  WHERE p.status AND NOT p.gmDisabled
  GROUP BY p.product_id
  ORDER BY p.product_id
";

foreach ($db->query($sql)->rows as $product) {
  $link = str_replace('&', '&amp;', $url->link('product/product', ['product_id' => $product['id']]));

  $images = [];
  foreach(json_decode($product['images'], true) as $image) $images[] = $modelImage->resize($image);
  $imageMain = array_shift($images);

  $name = $product['name'];
  $googleProductCategory = $product['gmDescription']
    ? str_replace('%name%', $name, $product['gmDescription'])
    : $name;

  $content .= '<item>';
  $content .= "<g:id>{$product['id']}</g:id>";
  $content .= '<g:title>' . mb_substr($name, 0, 149) . '</g:title>';
  $content .= "<g:description>{$googleProductCategory}</g:description>";
  $content .= "<g:link>{$link}</g:link>";
  $content .= "<g:image_link>{$imageMain}</g:image_link>";
  foreach($images as $image) $content .= "<g:additional_image_link>{$image}</g:additional_image_link>";
  $content .= "<g:availability>{$product['availability']}</g:availability>";
  $content .= "<g:price>{$product['price']} UAH</g:price>";
  if ($product['brand']) $content .= "<g:brand>{$product['brand']}</g:brand>";
  $content .= "<g:google_product_category>{$product['gmCategory']}</g:google_product_category>";
  $content .= "<g:product_type>{$product['category']}</g:product_type>";
  $content .= "<g:condition>new</g:condition>";
  if ($product['color']) $content .= "<g:color>{$product['color']}</g:color>";
  $content .= "<g:max_handling_time>0</g:max_handling_time>";
  $content .= "<g:shipping_weight>1 kg</g:shipping_weight>";
  $content .= '</item>';
}

$content .= "</channel>";
$content .= '</rss>';
file_put_contents(__DIR__ . '/../google-merchant.xml', $content);

$db = null;
