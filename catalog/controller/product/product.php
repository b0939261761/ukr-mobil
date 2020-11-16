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

    $data['headingH1'] = $product_info['name'];

    foreach ($images as $key=>$image) {
      $index = $key + 1;
      $indexImage = count($images) > 1 ? ", фото № {$index}" : '';
      $thumb = $this->model_tool_image->resize($image, 80, 80);
      $data['images'][] = [
        'link'  => "/image/{$image}",
        'thumb' => $thumb,
        'alt'   => "{$data['headingH1']}{$indexImage} - ukr-mobil.com",
        'title' => "{$data['headingH1']}{$indexImage}"
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

    $data['review_status'] = $this->config->get('config_review_status');
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
      "@context" => "https://schema.org/",
      "@type" => "Product",
      "name" => $data['headingH1'],
      "image" => [ $data['images'][0]['link'] ],
      "description" => $data['description'],
      "sku" => $data['product_id'],
      "offers" => [
        "@type" => "Offer",
        "url" => $productLink,
        "priceCurrency" => "UAH",
        "price" => $data['price_uah'],
        "priceValidUntil" => $priceValidUntil,
        "itemCondition" => "https://schema.org/NewCondition",
        "availability" => empty($data['productCount']) ? "https://schema.org/OutOfStock" : "https://schema.org/InStock"
      ]
    ];

    $this->document->addLink($productLink, 'canonical');
    $this->document->setTitle($product_info['meta_title']);
    $this->document->setDescription($product_info['meta_description']);
    $this->document->setKeywords($product_info['meta_keyword']);
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
    $this->load->language('product/product');

    $this->load->model('catalog/review');

    if (isset($this->request->get['page'])) {
      $page = $this->request->get['page'];
    } else {
      $page = 1;
    }

    $data['reviews'] = array();

    $review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

    $results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

    foreach ($results as $result) {
      $data['reviews'][] = array(
        'author' => $result['author'],
        'text' => nl2br($result['text']),
        'rating' => (int)$result['rating'],
        'date_added' => date('d.m.Y', strtotime($result['date_added']))
      );
    }

    $pagination = new Pagination();
    $pagination->total = $review_total;
    $pagination->page = $page;
    $pagination->limit = 5;
    $pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

    $data['pagination'] = $pagination->render();

    $data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('product/review', $data));
  }

  public function write() {
    $this->load->language('product/product');

    $json = [];

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
      if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
        $json['error'] = 'Имя должно быть от 3 до 25 символов!';
      }

      if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
        $json['error'] = 'Текст отзыва должен быть от 25 до 1000 символов!';
      }

      if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
        $json['error'] = 'Пожалуйста, выберите оценку!';
      }

      // Captcha
      if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
        $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

        if ($captcha) {
          $json['error'] = $captcha;
        }
      }

      if (!isset($json['error'])) {
        $this->load->model('catalog/review');

        $this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

        $json['success'] = 'Спасибо за ваш отзыв. Он поступил администратору для проверки на спам и вскоре будет опубликован.';
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }



  /**
   * Add product to wishlist
   *
   * @return mixed|string
   */
  public function addToWishlist() {
    $success = false;
    $msg = self::MSG_INTERNAL_ERROR;
    $code = 500;
    $data = [];

    try {
      //region Input Data
      $transferData = $this->getInput('transferData');
      //endregion

      //  Check authorized user
      if (!$this->customer->isLogged()) {
        throw new \RuntimeException('Only authorized users.', 401);
      }

      //  Product ID
      $productId = (int)Util::getArrItem($transferData, 'productId');

      if ($productId <= 0) {
        throw new \RuntimeException('Invalid product ID');
      }

      //region Define Models
      $customerWishlistModel = new \Ego\Models\CustomerWishlist();
      //endregion

      //  Check existing in wishlist
      if ($customerWishlistModel->exist((int)$this->customer->getId(), $productId)) {
        throw new \RuntimeException('Already in wishlist');
      }

      //  Add to wishlist
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

  /**
   * Return stock list for product
   *
   * @param $productId - Product ID
   * @return array
   */
  private function getStockList($productId) {
    //region Define Models
    $productModel = new \Ego\Models\Product();
    $egoProductToStockModel = new \Ego\Models\ProductToStock();
    $stocksModel = new Stocks();
    //endregion

    $product = $productModel->get($productId, true);

    //  Check empty product
    if (empty($product)) {
      return [];
    }

    //region Stock List
    $stockList = [
      [
        'name' => 'г. Черновцы',
        'quantity' => $product->getQuantity()
      ]
    ];

    $productToStockList = $egoProductToStockModel->getListByProduct($product->getProductId(), true);
    $productToStockList = empty($productToStockList) ? [] : $productToStockList;

    foreach ($productToStockList as $productToStock) {
      $stockRow = $stocksModel->get($productToStock->getStockId(), true);

      if (empty($stockRow)) {
        continue;
      }

      $stockList[] = [
        'name' => $stockRow->getName(),
        'quantity' => $productToStock->getQuantity()
      ];
    }
    //endregion

    return $stockList;
  }

  /**
   * Return product count
   *
   * @param $productId - Product ID
   * @return int
   */
  private function getProductCount($productId) {
    $productId = (int)$productId;

    //region Define Models
    $productModel = new \Ego\Models\Product();
    $egoProductToStockModel = new \Ego\Models\ProductToStock();
    $stocksModel = new Stocks();
    //endregion

    return $egoProductToStockModel->getCount($productId);
  }

    // public function getRecurringDescription() {
  //   $this->load->language('product/product');
  //   $this->load->model('catalog/product');

  //   if (isset($this->request->post['product_id'])) {
  //     $product_id = $this->request->post['product_id'];
  //   } else {
  //     $product_id = 0;
  //   }

  //   if (isset($this->request->post['recurring_id'])) {
  //     $recurring_id = $this->request->post['recurring_id'];
  //   } else {
  //     $recurring_id = 0;
  //   }

  //   if (isset($this->request->post['quantity'])) {
  //     $quantity = $this->request->post['quantity'];
  //   } else {
  //     $quantity = 1;
  //   }

  //   $product_info = $this->model_catalog_product->getProduct($product_id);

  //   $recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

  //   $json = array();

  //   if ($product_info && $recurring_info) {
  //     if (!$json) {
  //       $frequencies = array(
  //         'day' => $this->language->get('text_day'),
  //         'week' => $this->language->get('text_week'),
  //         'semi_month' => $this->language->get('text_semi_month'),
  //         'month' => $this->language->get('text_month'),
  //         'year' => $this->language->get('text_year'),
  //       );

  //       if ($recurring_info['trial_status'] == 1) {
  //         $price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
  //         $trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
  //       } else {
  //         $trial_text = '';
  //       }

  //       $price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

  //       if ($recurring_info['duration']) {
  //         $text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
  //       } else {
  //         $text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
  //       }

  //       $json['success'] = $text;
  //     }
  //   }

  //   $this->response->addHeader('Content-Type: application/json');
  //   $this->response->setOutput(json_encode($json));
  // }
}
