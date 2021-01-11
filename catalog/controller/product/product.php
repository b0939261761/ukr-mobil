<?
use Ego\Controllers\BaseController;
use Ego\Models\Stocks;
use Ego\Providers\Util;

class ControllerProductProduct extends BaseController {
  // Костыль что бы вернуть закешированное изображение
  public function picture() {
    $this->load->model('tool/image');
    $this->load->model('catalog/product');

    $productId = $this->request->get['id'];
    $product = $this->model_catalog_product->getProduct($productId);

    $imageFileName = $product['image'] ? $product['image'] : 'placeholder.png';
    $image = $this->model_tool_image->resize($imageFileName, 50, 50);
    $path_url = parse_url($image, PHP_URL_PATH);
    $path_image = __DIR__ . "/../../..{$path_url}";
    $imagedata = file_get_contents($path_image);
    $ext = pathinfo($path_image, PATHINFO_EXTENSION);

    $this->response->addHeader("Content-Type: image/{$ext}");
    $this->response->setOutput($imagedata);
  }

  private function getProductProperties(int $productId) {
    $sql = "
      SELECT cc.id, cc.name
      FROM products_properties aa
      LEFT JOIN product_property_values bb ON bb.id = aa.product_property_value_id
      LEFT JOIN product_properties cc ON cc.id = bb.product_property_id
      WHERE aa.product_id = {$productId}
      GROUP BY bb.product_property_id
      ORDER BY cc.ord
    ";
    return $this->db->query($sql)->rows;
  }

  private function getProductPropertyValues(int $productId, int $productPropertyId) {
    $sql = "
      SELECT aa.product_id, bb.name, bb.color, aa.active, aa.available, aa.product_id_link
      FROM products_properties aa
      LEFT JOIN product_property_values bb ON bb.id = aa.product_property_value_id
      WHERE aa.product_id = {$productId} AND bb.product_property_id = {$productPropertyId}
      ORDER BY bb.ord
    ";
    return $this->db->query($sql)->rows;
  }

  private function getProductsProperties(int $productId) {
    $product_properties = [];

    foreach ($this->getProductProperties($productId) as $property) {
      $values = [];

      foreach ($this->getProductPropertyValues($productId, $property['id'] ) as $value) {
        $values[] = [
          'name' => $value['name'],
          'color' => $value['color'],
          'active' => $value['active'],
          'available' => $value['available'],
          'link' => $this->url->link('product/product', ['product_id' => $value['product_id_link']])
        ];
      }

      $product_properties[] = [
        'name' => $property['name'],
        'values' => $values
      ];
    }

    return $product_properties;
  }

  public function index() {
    $this->load->language('product/product');
    $this->load->model('catalog/product');
    $this->load->model('tool/image');

    $productId = (int)($this->request->get['product_id'] ?? 0);
    $product_info = $this->model_catalog_product->getProduct($productId);
    if (!$product_info) $this->response->redirect($this->url->link('error/not_found'));

    // Seo Tags Generator.Begin
    $stg_data = [ 'attribute_groups' => [], 'product_info' => $product_info ];
    $product_info = $this->load->controller('extension/module/stg_helper/getProductTags', $stg_data);
    // Seo Tags Generator.End

    $data['headingH1'] = $product_info['name'];
    $data['description'] = html_entity_decode($product_info['description']);

    $data['product_properties'] = $this->getProductsProperties($productId);

    $customerGroupId = (int)($this->customer->getGroupId() ?? 1);
    $customerId = $this->customer->getId() ?? 0;

    $sql = "
      SELECT
        p.product_id AS id,
        b.name AS brandName,
        p.quantity AS quantityStore1,
        p.quantity_store_2 AS quantityStore2,
        p.quantity + p.quantity_store_2 AS quantity,
        COALESCE(pdc.price, p.price) AS priceUSD,
        ROUND(COALESCE(pdc.price, p.price) * c.value) AS priceUAH,
        (SELECT price
          FROM oc_product_special
          WHERE product_id = p.product_id
            AND customer_group_id = {$customerGroupId}
            AND (date_start = '0000-00-00' OR date_start < NOW())
            AND (date_end = '0000-00-00' OR date_end > NOW())
          ORDER BY priority ASC, price ASC LIMIT 1
        ) AS specialUSD,
        ROUND((SELECT price
          FROM oc_product_special
          WHERE product_id = p.product_id
            AND customer_group_id = {$customerGroupId}
            AND (date_start = '0000-00-00' OR date_start < NOW())
            AND (date_end = '0000-00-00' OR date_end > NOW())
          ORDER BY priority ASC, price ASC LIMIT 1
        ) * c.value) AS specialUAH,
        (SELECT COUNT(1) AS cnt FROM oc_customer_wishlist
          WHERE customer_id = {$customerId} AND product_id = p.product_id) > 0 AS isWishlist,
        CASE
          WHEN p.image = '' AND NOT JSON_LENGTH(tmpImages.images) THEN JSON_ARRAY('placeholder.png')
          WHEN p.image != '' THEN JSON_ARRAY_INSERT(tmpImages.images, '$[0]', p.image)
          ELSE tmpImages.images
        END AS images
      FROM oc_product p
      LEFT JOIN oc_product_discount pdc ON pdc.product_id = p.product_id
        AND pdc.customer_group_id = {$customerGroupId}
      LEFT JOIN oc_currency c ON c.currency_id = 980
      LEFT JOIN products_models pm ON pm.product_id = p.product_id
      LEFT JOIN models m ON m.id = pm.model_id
      LEFT JOIN brands b ON b.id = m.brand_id
      LEFT JOIN (
        SELECT
          IF(COUNT(image), JSON_ARRAYAGG(image), JSON_ARRAY()) AS images
        FROM oc_product_image
        WHERE product_id = {$productId}
        ORDER BY sort_order
      ) AS tmpImages ON true
      WHERE p.product_id = {$productId}
      LIMIT 1
    ";
    $data['product'] = $this->db->query($sql)->row;

    $productImages = json_decode($data['product']['images'], true);
    foreach ($productImages as $key=>$image) {
      $index = $key + 1;
      $indexImage = count($productImages) > 1 ? ", фото № {$index}" : '';
      $data['images'][] = [
        'link'      => $this->model_tool_image->resize($image, 1024, 1024, true),
        'preview'   => $this->model_tool_image->resize($image, 540, 256),
        'thumb'     => $this->model_tool_image->resize($image, 80, 80),
        'alt'       => "{$data['headingH1']}{$indexImage} - ukr-mobil.com",
        'title'     => "{$data['headingH1']}{$indexImage}"
      ];
    }

    // rating and review

    $sql = "
      SELECT
        COUNT(*) AS quantity,
        avg(r.rating) AS avgRating,
        max(r.rating) AS maxRating,
        min(r.rating) AS minRating,
        list.list
      FROM oc_review r
      LEFT JOIN (
        SELECT
          JSON_ARRAYAGG(JSON_OBJECT(
            'author', t.author, 'rating', t.rating, 'text', t.text, 'date', t.date
          )) AS list
        FROM (
          SELECT
            author,
            rating,
            text,
            DATE_FORMAT(date_added, '%Y-%m-%d') AS date
          FROM oc_review
          WHERE product_id = {$productId} AND status
          ORDER BY date_added DESC
          LIMIT 5
        ) AS t
      ) AS list ON true
      WHERE product_id = {$productId} AND status
    ";

    $reviews = $this->db->query($sql)->row;
    $data['reviewsQuantity'] = $reviews['quantity'];

    // ------------------- related
    $data['products'] = [];
    $productCategoryId = (new \Ego\Models\ProductToCategory())->getProductCategory($productId);
    $relatedCategoryId = (new \Ego\Models\CategoryPath())->getRoot($productCategoryId);

    $results = $this->model_catalog_product->getProducts([
      'filter_category_id' => $relatedCategoryId,
      'filter_sub_category' => true,
      'sort' => 'RAND()',
      'start' => 0,
      'limit' => 6
    ]);

    //  Shuffle and cut related products
    if (count($results) >= 6) {
      shuffle($results);
      $results = array_splice($results, 0, 15);
    }

    foreach ($results as $result) {
      $image = $this->model_tool_image->resize($result['image'] ? $result['image'] : 'placeholder.png', 350, 350);
      $images = $this->model_catalog_product->getProductImages($result['product_id']);
      $images = isset($images[0]['image']) && !empty($images) ? $images[0]['image'] : $image;

      $price = $this->customer->isLogged() ? $this->currency->format($result['price']) : false;
      $special = $result['special'] ? $this->currency->format($result['special']) : false;

      $data['products'][] = [
        'product_id' => $result['product_id'],
        'thumb' => $image,
        'name' => $result['name'],
        'thumb_swap' => $this->model_tool_image->resize($images, 350, 350),
        'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
        'price' => $price,
        'special' => $special,
        'href' => $this->url->link('product/product', ['product_id' => $result['product_id']]),
      ];
    }
    // ------------------- end related

    $sql = "
      SELECT
        REPLACE(LOWER(ep.ep_category), 'product_', '') AS category,
        epc.epc_content AS content
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) IN ('product_warranty', 'product_delivery')
    ";

    foreach ($this->db->query($sql)->rows as $post) $data[$post['category']] = $post['content'];

    $this->document->setTitle($product_info['meta_title']);
    $this->document->setDescription($product_info['meta_description']);

    $data['breadcrumbs'] = $this->getBreadcrumbs($productId);
    $this->document->setMicrodataBreadcrumbs($data['breadcrumbs']);

    $microdata = $this->getMicrodata($data, $productImages, $reviews);
    $this->document->setMicrodata(json_encode($microdata));

    $this->document->addMeta(['property' => 'og:locale', 'content' => 'ru_UA']);
    $this->document->addMeta(['property' => 'og:title', 'content' => $product_info['meta_title']]);
    $this->document->addMeta([
      'property' => 'og:description',
      'content' => $product_info['meta_description']
    ]);
    $this->document->addMeta([
      'property' => 'og:url',
      'content'  => $this->url->link('product/product', ['product_id' => $productId])
    ]);
    $this->document->addMeta([
      'property' => 'og:image',
      'content' => $this->model_tool_image->resize($productImages[0], 1024, 1024)
    ]);

    $data['reviews'] = $this->getReviews($productId);
    $data['customerFullname'] = $this->customer->isLogged()
      ? "{$this->customer->getFirstName()}&nbsp;{$this->customer->getLastName()}"
      : '';

    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('product/product', $data));
  }

  private function getMicrodata($data, $productImages, $reviews) {
    $category = implode(array_map(function($item) { return $item['name']; }, $data['breadcrumbs']), ' ');
    $availability = 'https://schema.org/' . ($data['product']['quantity'] ? 'In' : 'OutOf') . 'Stock';

    $microdata = [
      '@context'    => 'https://schema.org/',
      '@type'       => 'Product',
      'url'         => $this->url->link('product/product', ['product_id' => $data['product']['id']]),
      'category'    => $category,
      'image'       => $this->model_tool_image->resize($productImages[0], 1024, 1024),
      'name'        => $data['headingH1'],
      'description' => $data['headingH1'],
      'offers'      => [
        '@type'           => 'Offer',
        'availability'    => $availability,
        'price'           => $data['product']['priceUAH'],
        'priceCurrency'   => 'UAH'
      ]
    ];

    if ($data['product']['brandName']) $microdata['brand'] = $data['product']['brandName'];
    if (!$reviews['quantity']) return $microdata;

    $microdata['aggregateRating'] = [
      '@type'       => 'AggregateRating',
      'ratingValue' => $reviews['avgRating'],
      'bestRating'  => $reviews['maxRating'],
      'worstRating' => $reviews['minRating'],
      'ratingCount' => $reviews['quantity']
    ];

    foreach(json_decode($reviews['list'], true) as $item) {
      $microdata['review'][] = [
        '@type'         => 'Review',
        'author'        => $item['author'],
        'datePublished' => $item['date'],
        'description'   => $item['text'],
        'name'          => $item['text'],
        'reviewRating'  => [
          '@type'       => 'Rating',
          'bestRating'  => 5,
          'ratingValue' => $item['rating'],
          'worstRating' => 1
        ]
      ];
    }
    return $microdata;
  }

  private function getBreadcrumbs($productId) {
    $sql = "
      SELECT c.category_id, cd.name
      FROM oc_product_to_category ptc
      LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
      LEFT JOIN oc_category c ON c.category_id = cp.path_id
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE ptc.product_id = {$productId} AND cd.language_id = 2 AND c.status = 1
      ORDER BY cp.level
    ";

    foreach ($this->db->query($sql)->rows as $category) {
      $path[] = $category['category_id'];
      $breadcrumbs[] = [
        'name' => $category['name'],
        'link' => $this->url->link('product/category', ['path' => implode('_', $path)])
      ];
    }
    return $breadcrumbs ?? [];
  }

  private function getReviews($productId, $page = 1) {
    $limit = 5;
    $start = ($page - 1) * $limit;

    $sql = "
      SELECT
        review_id,
        author,
        rating,
        text,
        DATE_FORMAT(date_added, '%d.%m.%Y') AS date
      FROM oc_review
      WHERE product_id = {$productId} AND status
      ORDER BY date_added DESC
      LIMIT {$start}, {$limit}
    ";

    $data['reviews'] = $this->db->query($sql)->rows;
    foreach ($data['reviews'] as &$item) $item['text'] = nl2br($item['text']);

    $sql = "SELECT COUNT(*) AS total FROM oc_review WHERE product_id = {$productId} AND status";
    $total = $this->db->query($sql)->row['total'];

    $pagination = new Pagination();
    $pagination->total = $total;
    $pagination->page = $page;
    $pagination->limit = $limit;
    $query = ['productId' => $productId, 'page' => '{page}'];
    $pagination->url = $this->url->link('product/product/review', $query);
    $data['pagination'] = $pagination->render();

    return $this->load->view('product/review', $data);
  }

  public function review() {
    $productId = (int)($this->request->get['productId'] ?? 0);
    $page = (int)($this->request->get['page'] ?? 1);
    $this->response->setOutput($this->getReviews($productId, $page));
  }

  public function reviewAdd() {
    $requestData = json_decode(file_get_contents('php://input'), true);

    $customerId = (int)$this->customer->getId();
    $name = $this->db->escape($requestData['name'] ?? '');
    $text = $this->db->escape($requestData['text'] ?? '');
    $rating = (int)($requestData['rating'] ?? 0);
    $productId = (int)($requestData['productId'] ?? 0);

    $sql = "
      INSERT INTO oc_review (
        author, customer_id, product_id, text, rating, date_added
      ) VALUES (
        '{$name}', {$customerId}, {$productId}, '{$text}', {$rating}, NOW()
      )
    ";

    $this->db->query($sql);
    $this->response->setOutput('');
  }

  public function addToWishlist() {
    $success = false;
    $msg = self::MSG_INTERNAL_ERROR;
    $code = 500;
    $data = [];

    try {
      $transferData = $this->getInput('transferData');

      if (!$this->customer->isLogged()) {
        throw new \RuntimeException('Only authorized users.', 401);
      }

      $productId = (int)Util::getArrItem($transferData, 'productId');

      if ($productId <= 0) {
        throw new \RuntimeException('Invalid product ID');
      }

      $customerWishlistModel = new \Ego\Models\CustomerWishlist();

      if ($customerWishlistModel->exist((int)$this->customer->getId(), $productId)) {
        throw new \RuntimeException('Already in wishlist');
      }

      $row = (new \Ego\Struct\CustomerWishlistRowStruct())
        ->setCustomerId((int)$this->customer->getId())
        ->setProductId($productId);

      if (!$customerWishlistModel->add($row)) {
        throw new \RuntimeException('Error occurred while add to wishlist.');
      }

      $success = true;
      $msg = self::MSG_SUCCESS;
      $code = 200;
    } catch (\Exception $ex) {
      $msg = $ex->getMessage();
      $code = $ex->getCode();
      $data = [];
    }

    return $this->_prepareJson([
      'success' => $success,
      'msg' => $msg,
      'code' => $code,
      'data' => $data
    ]);
  }
}
