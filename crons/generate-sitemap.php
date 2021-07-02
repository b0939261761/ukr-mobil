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

$sitemap = $sitemapImage = '<?xml version="1.0" encoding="UTF-8"?>';
$sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$sitemapImage .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
$sitemapImage .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

$sql = "
  WITH
    tmpProduct AS (
      SELECT
        p.product_id as id,
        p.image,
        IF(count(t.image), JSON_ARRAYAGG(t.image), JSON_ARRAY()) AS images
      FROM oc_product p
      LEFT JOIN LATERAL (
        SELECT image FROM oc_product_image pi
        WHERE pi.product_id = p.product_id
        ORDER BY pi.sort_order
      ) AS t ON true
      WHERE p.status = 1
      GROUP BY p.product_id
    )
    SELECT id, IF(image != '', JSON_ARRAY_INSERT(images, '$[0]', image), images) AS images
    FROM tmpProduct
";

$errorFileImage = './error_exists_file_image.txt';
if (is_file($errorFileImage)) unlink($errorFileImage);

$i = 0;
foreach ($db->query($sql)->rows as $product) {
  $link = str_replace('&', '&amp;', $url->link('product', ['product_id' => $product['id']]));
  $loc = "<loc>{$link}</loc>";
  $sitemap .= "<url>{$loc}</url>";
  $images = json_decode($product['images'], true);

  if (empty($images)) continue;
  $sitemapImage .= "<url>{$loc}";
  foreach ($images as $image) {
    if (!is_file(DIR_IMAGE . $image)) {
      file_put_contents($errorFileImage, "{$image}\n", FILE_APPEND);
      continue;
    }

    $urlImage = $modelImage->resize($image, 0, 0, true);
    $sitemapImage .= "<image:image><image:loc>{$urlImage}</image:loc></image:image>";
  }

  $sitemapImage .= "</url>";
}

$sql = "
  WITH
    tmpCatagoriesBrandsModels AS (
      SELECT
        cp.path_id AS category_id,
        m.brand_id,
        b.name AS brand_name,
        b.ord AS brand_ord,
        pm.model_id,
        m.name AS model_name,
        m.ord AS model_ord
      FROM products_models pm
      LEFT JOIN oc_product_to_category ptc ON ptc.product_id = pm.product_id
      LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
      LEFT JOIN models m ON m.id = pm.model_id
      LEFT JOIN brands b ON b.id = m.brand_id
      GROUP BY cp.path_id, m.brand_id, pm.model_id
    ),
    tmpGroupCategoriesBrands AS (
      SELECT DISTINCT category_id, brand_id, brand_name, brand_ord FROM tmpCatagoriesBrandsModels
    ),
    tmpModels AS (
      SELECT
        category_id,
        brand_id,
        brand_name,
        brand_ord,
        JSON_ARRAYAGG(JSON_OBJECT('id', t.id, 'name', t.name)) as models
      FROM tmpGroupCategoriesBrands tgcb,
      LATERAL (
        SELECT
          model_id AS id,
          model_name AS name
        FROM tmpCatagoriesBrandsModels
        WHERE category_id = tgcb.category_id AND brand_id = tgcb.brand_id
        ORDER BY model_ord, model_name
      ) AS t
      GROUP BY category_id, brand_id
    ),
    tmpGroupCategories AS (
      SELECT DISTINCT category_id FROM tmpCatagoriesBrandsModels
    ),
    tmpBrands AS (
      SELECT
        category_id,
        JSON_ARRAYAGG(JSON_OBJECT('id', t.id, 'name', t.name, 'models', t.models)) AS brands
      FROM tmpGroupCategories tgc,
      LATERAL (
        SELECT
          brand_id AS id,
          brand_name AS name,
          (SELECT models FROM tmpModels WHERE category_id = tm.category_id
            AND brand_id = tm.brand_id) AS models
        FROM tmpModels tm
        WHERE category_id = tgc.category_id
        ORDER BY brand_ord, brand_name
        ) AS t
      GROUP BY category_id
    )
  SELECT
    IF(count(path), JSON_ARRAYAGG(JSON_OBJECT('path', path, 'name', name, 'brands', brands, 'children', children)), JSON_ARRAY()) AS value
  FROM (
    SELECT
      c1.category_id AS path,
      cd1.name,
      COALESCE(tb1.brands, JSON_ARRAY()) AS brands,
      IF(count(t2.path), JSON_ARRAYAGG(JSON_OBJECT('path', t2.path, 'name', t2.name, 'brands', t2.`brands`, 'children', t2.children)), JSON_ARRAY()) AS children
      FROM oc_category c1
    LEFT JOIN oc_category_description cd1 ON cd1.category_id = c1.category_id
    LEFT JOIN tmpBrands tb1 ON tb1.category_id = c1.category_id
    LEFT JOIN LATERAL (
      SELECT
        CONCAT(c1.category_id, '_', c2.category_id) AS path,
        cd2.name,
        COALESCE(tb2.brands, JSON_ARRAY()) AS brands,
        IF(count(t3.path), JSON_ARRAYAGG(JSON_OBJECT('path', t3.path, 'name', t3.name, 'brands', t3.brands)), JSON_ARRAY()) AS children
      FROM oc_category c2
      LEFT JOIN oc_category_description cd2 ON cd2.category_id = c2.category_id
      LEFT JOIN tmpBrands tb2 ON tb2.category_id = c2.category_id
      LEFT JOIN LATERAL (
        SELECT
          CONCAT(c1.category_id, '_', c2.category_id, '_', c3.category_id) AS path,
          cd3.name,
          COALESCE(tb3.brands, JSON_ARRAY()) AS brands
        FROM oc_category c3
        LEFT JOIN oc_category_description cd3 ON cd3.category_id = c3.category_id
        LEFT JOIN tmpBrands tb3 ON tb3.category_id = c3.category_id
        WHERE c3.parent_id = c2.category_id AND cd3.language_id = 2 AND c3.status = 1
        ORDER BY c3.sort_order, cd3.name
      ) AS t3 ON true
      WHERE c2.parent_id = c1.category_id AND cd2.language_id = 2 AND c2.status = 1
      GROUP BY c2.category_id
      ORDER BY c2.sort_order, cd2.name
    ) AS t2 ON true
    WHERE c1.parent_id = 0 AND cd1.language_id = 2 AND c1.status = 1
    GROUP BY c1.category_id
    ORDER BY c1.sort_order, cd1.name
  ) AS t1
";

function getLinks($categories, $url) {
  $sitemap = '';
  foreach ($categories as $category) {
    $queriesCategory = ['path' => $category['path']];
    $linkCategory = str_replace('&', '&amp;', $url->link('product/category', $queriesCategory));
    $sitemap .= "<url><loc>{$linkCategory}</loc></url>";

    foreach ($category['brands'] as $brand) {
      $queriesBrand = array_merge($queriesCategory, ['brand' => $brand['id']]);
      $linkBrand = str_replace('&', '&amp;', $url->link('product/category', $queriesBrand));
      $sitemap .= "<url><loc>{$linkBrand}</loc></url>";

      foreach ($brand['models'] as $model) {
        $queriesModel = array_merge($queriesBrand, ['model' => $model['id']]);
        $linkModel = str_replace('&', '&amp;', $url->link('product/category', $queriesModel));
        $sitemap .= "<url><loc>{$linkModel}</loc></url>";
      }
    }

    if (isset($category['children'])) $sitemap .= getLinks($category['children'], $url);
  }

  return $sitemap;
}

$categories = json_decode($db->query($sql)->row['value'], true);
$sitemap .= getLinks($categories, $url);
$sitemap .= '</urlset>';
file_put_contents(__DIR__ . '/../sitemap.xml', $sitemap);

$sitemapImage .= '</urlset>';
file_put_contents(__DIR__ . '/../sitemapimages.xml', $sitemapImage);

$db = null;
