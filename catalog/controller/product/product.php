<?
use Ego\Controllers\BaseController;
use Ego\Models\Stocks;
use Ego\Providers\Util;

class ControllerProductProduct extends BaseController {
  // Костыль что бы вернуть закешированное изображение
  public function picture() {
    $this->load->model('tool/image');
    $this->load->model('catalog/product');

    $product_id = $this->request->get['id'];
    $product = $this->model_catalog_product->getProduct($product_id);

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

    $product_id = (int)($this->request->get['product_id'] ?? 0);
    $product_info = $this->model_catalog_product->getProduct($product_id);

    if (!$product_info) $this->response->redirect($this->url->link('error/not_found'));

    // Seo Tags Generator.Begin
    $stg_data = [ 'attribute_groups' => [], 'product_info' => $product_info ];
    $product_info = $this->load->controller('extension/module/stg_helper/getProductTags', $stg_data);
    // Seo Tags Generator.End

    $this->load->model('catalog/review');
    $customerWishlistModel = new \Ego\Models\CustomerWishlist();
    $categoryPathModel = new \Ego\Models\CategoryPath();
    $productToCategoryModel = new \Ego\Models\ProductToCategory();

    $data['product_properties'] = $this->getProductsProperties($product_id);

    $data['text_login'] = "Пожалуйста <a href=\"{$this->url->link('account/login')}\">авторизируйтесь</a>
      или <a href=\"{$this->url->link('account/register')}\">создайте учетную запись</a>
      перед тем как написать отзыв";

    $data['tab_review'] = "Отзывов ({$product_info['reviews']})";
    $data['product_id'] = $product_id;
    $data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

    if ($product_info['quantity'] <= 0) {
      $data['stock'] = $product_info['stock_status'];
    } elseif ($this->config->get('config_stock_display')) {
      $data['stock'] = $product_info['quantity'];
    } else {
      $data['stock'] = 'Есть в наличии';
    }

    $this->load->model('tool/image');

    $images[] = $product_info['image'] ? $product_info['image'] : 'placeholder.png';
    foreach ($this->model_catalog_product->getProductImages($product_id) as $image) $images[] = $image['image'];

    $microdataImage = $this->model_tool_image->resize($images[0], 0, 0, true);
    $data['headingH1'] = $product_info['name'];

    foreach ($images as $key=>$image) {
      $index = $key + 1;
      $indexImage = count($images) > 1 ? ", фото № {$index}" : '';
      $data['images'][] = [
        'link'    => $this->model_tool_image->resize($image, 0, 0, true),
        'preview' => $this->model_tool_image->resize($image, 540, 256),
        'thumb'   => $this->model_tool_image->resize($image, 80, 80),
        'alt'     => "{$data['headingH1']}{$indexImage} - ukr-mobil.com",
        'title'   => "{$data['headingH1']}{$indexImage}"
      ];
    }

    $tax_class_id = $product_info['tax_class_id'];
    $config_tax = $this->config->get('config_tax');

    $currencyModel = new \Ego\Models\Currency();
    $currency_course = $currencyModel->get('UAH', true);
    $currency_course = empty($currency_course) ? '' : $currency_course->getValue();

    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
      $price_usd = $product_info['price'];
      $data['price_usd'] = number_format(round($price_usd, 2), 2, '.', '');
      $data['price_uah'] = number_format(round($price_usd * $currency_course, 0), 0, '.', '');
    }

    if ((float)$product_info['special']) {
      $special_usd = $this->tax->calculate($product_info['special'], $tax_class_id, $config_tax);
      $data['special_usd'] = number_format(round($special_usd, 2), 2, '.', '');
      $data['special_uah'] = number_format(round($special_usd * $currency_course, 0), 0, '.', '');
    }

    // $data['review_status'] = $this->config->get('config_review_status');
    $data['review_guest'] = $this->config->get('config_review_guest') || $this->customer->isLogged();

    $data['customer_name'] = $this->customer->isLogged()
      ? $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName()
      : '';

    $data['reviews'] = "{$product_info['reviews']} отзывов";
    $data['rating'] = (int)$product_info['rating'];


    $data['products'] = [];

    //  Get related products from current or parent category of the product
    $productCategoryId = $productToCategoryModel->getProductCategory($product_id);
    $relatedCategoryId = $categoryPathModel->getRoot($productCategoryId);

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
      if ($result['image']) {
        $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
      } else {
        $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
      }
      //added for image swap

      $images = $this->model_catalog_product->getProductImages($result['product_id']);

      if (isset($images[0]['image']) && !empty($images)) {
        $images = $images[0]['image'];
      } else {
        $images = $image;
      }

      $price = $this->customer->isLogged() ? $this->currency->format($result['price']) : false;
      $special = $result['special'] ? $this->currency->format($result['special']) : false;


      $data['products'][] = [
        'product_id' => $result['product_id'],
        'thumb' => $image,
        'name' => $result['name'],
        'thumb_swap' => $this->model_tool_image->resize($images, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
          $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')),
        'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
        'price' => $price,
        'special' => $special,
        'href' => $this->url->link('product/product', 'product_id=' . $result['product_id']),
      ];
    }


    $data['stockList'] = $this->getStockList($product_id);
    $data['productCount'] = $this->getProductCount($product_id);
    $data['productInWishlist'] = $customerWishlistModel->exist((int)$this->customer->getId(), $product_id);

    $postModel = new \Ego\Models\EgoPost();
    $postContentModel = new \Ego\Models\EgoPostContent();
    $currentLangId = $this->config->get('config_language_id');

    $data['delivery'] = '';
    $post = $postModel->getByCategory('product_delivery', true);

    if (!empty($post)) {
      $postContent = $postContentModel->getByPost($post[0]->getId(), $currentLangId, false, true);
      $data['delivery'] = empty($postContent) ? '' : $postContent[0]->getContent();
    }

    // ----------------------------------

    $data['warranty'] = '';
    $post = $postModel->getByCategory('product_warranty', true);

    if (!empty($post)) {
      $postContent = $postContentModel->getByPost($post[0]->getId(), $currentLangId, false, true);
      $data['warranty'] = empty($postContent) ? '' : $postContent[0]->getContent();
    }

    $productLink = $this->url->link('product/product', ['product_id' => $product_id]);

    $date = new DateTime();
    $date->add(new DateInterval('P1D'));
    $priceValidUntil = $date->format('Y-m-d');

    $microdata = [
      "@context"    => "https://schema.org/",
      "@type"       => "Product",
      "name"        => $data['headingH1'],
      "image"       => [ $microdataImage ],
      "description" => $data['description'],
      "sku"         => $data['product_id'],
      "offers"      => [
        "@type"           => "Offer",
        "url"             => $productLink,
        "priceCurrency"   => "UAH",
        "price"           => $data['price_uah'],
        "priceValidUntil" => $priceValidUntil,
        "itemCondition"   => "https://schema.org/NewCondition",
        "availability"    => empty($data['productCount']) ? "https://schema.org/OutOfStock" : "https://schema.org/InStock"
      ]
    ];

    $this->document->addLink($productLink, 'canonical');
    $this->document->setTitle($product_info['meta_title']);
    $this->document->setDescription($product_info['meta_description']);
    $this->document->setMicrodata(json_encode($microdata));
    $data['breadcrumbs'] = $this->getBreadcrumbs($product_id);
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('product/product', $data));
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

    $breadcrumbs = [];
    $path = [];
    foreach ($this->db->query($sql)->rows as $category) {
      $path[] = $category['category_id'];
      $breadcrumbs[] = [
        'text' => $category['name'],
        'link' => $this->url->link('product/category', ['path' => implode('_', $path)])
      ];
    }
    return $breadcrumbs;
  }

  public function review() {
    $productId = (int)($this->request->get['productId'] ?? 0);
    $page = (int)($this->request->get['page'] ?? 1);

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

    $this->response->setOutput($this->load->view('product/review', $data));
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

  private function getStockList($productId) {
    $product = (new \Ego\Models\Product())->get($productId, true);
    if (empty($product)) return [];

    $stockList = [
      [
        'name' => 'г. Черновцы',
        'quantity' => $product->getQuantity()
      ]
    ];

    $productToStockList = (new \Ego\Models\ProductToStock())->getListByProduct($product->getProductId(), true) ?? [];

    foreach ($productToStockList as $productToStock) {
      $stockRow = (new Stocks())->get($productToStock->getStockId(), true);

      if (empty($stockRow)) continue;

      $stockList[] = [
        'name' => $stockRow->getName(),
        'quantity' => $productToStock->getQuantity()
      ];
    }

    return $stockList;
  }

  private function getProductCount($productId) {
    return (new \Ego\Models\ProductToStock())->getCount($productId);
  }
}
