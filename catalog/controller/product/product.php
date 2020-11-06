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

		$data['mytemplate'] = $this->config->get('theme_default_directory');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');


		$this->load->model('catalog/product');

		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
		$product_info = $this->model_catalog_product->getProduct($product_id);

		if (!$product_info) {
			$this->document->setTitle($this->language->get('text_error'));
			$data['continue'] = $this->url->link('common/home');
      $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
      $data['header'] = $this->load->controller('common/header');
			return $this->response->setOutput($this->load->view('error/not_found', $data));
		}

    // Seo Tags Generator.Begin
    $stg_data = [ 'attribute_groups' => [], 'product_info' => $product_info ];
    $product_info = $this->load->controller('extension/module/stg_helper/getProductTags', $stg_data);
    // Seo Tags Generator.End

		$this->load->model('catalog/review');
		$customerWishlistModel = new \Ego\Models\CustomerWishlist();
		$categoryPathModel = new \Ego\Models\CategoryPath();
		$productToCategoryModel = new \Ego\Models\ProductToCategory();

		$this->document->addLink($this->url->link('product/product', 'product_id=' . $product_id), 'canonical');

		$data['product_properties'] = $this->getProductsProperties($product_id);
		$data['heading_title'] = $product_info['name'];
		$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
		$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);
		$data['product_id'] = $product_id;
		$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

		if ($product_info['quantity'] <= 0) {
			$data['stock'] = $product_info['stock_status'];
		} elseif ($this->config->get('config_stock_display')) {
			$data['stock'] = $product_info['quantity'];
		} else {
			$data['stock'] = $this->language->get('text_instock');
		}

		$this->load->model('tool/image');

    $image_thumb_width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width');
    $image_thumb_height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height');

    $imageFileName = $product_info['image'] ? $product_info['image'] : 'placeholder.png';
    $data['images'][] = $this->model_tool_image->resize($imageFileName, $image_thumb_width, $image_thumb_height);

    foreach ($this->model_catalog_product->getProductImages($product_id) as $image) {
			$data['images'][] = $this->model_tool_image->resize($image['image'], $image_thumb_width, $image_thumb_height);
		}

		$decimal_point = $this->language->get('decimal_point');
		$thousand_point = $this->language->get('thousand_point');
		$tax_class_id = $product_info['tax_class_id'];
		$config_tax = $this->config->get('config_tax');

		$currencyModel = new \Ego\Models\Currency();
		$currency_course = $currencyModel->get('UAH', true);
		$currency_course = empty($currency_course) ? '' : $currency_course->getValue();

		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$price_usd = $this->tax->calculate($product_info['price'], $tax_class_id, $config_tax);
			$data['price_usd'] = number_format(round($price_usd, 2), 2, $decimal_point, $thousand_point);
			$data['price_uah'] = number_format(round($price_usd * $currency_course, 0), 0, $decimal_point, $thousand_point);
		}

		if ((float)$product_info['special']) {
			$special_usd = $this->tax->calculate($product_info['special'], $tax_class_id, $config_tax);
			$data['special_usd'] = number_format(round($special_usd, 2), 2, $decimal_point, $thousand_point);
			$data['special_uah'] = number_format(round($special_usd * $currency_course, 0), 0, $decimal_point, $thousand_point);
		}

		if ($this->config->get('config_tax')) {
			$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
		} else {
			$data['tax'] = false;
		}

		$data['minimum'] = $product_info['minimum'] ? $product_info['minimum'] : 1;
		$data['review_status'] = $this->config->get('config_review_status');
		$data['review_guest'] = $this->config->get('config_review_guest') || $this->customer->isLogged();

		if ($this->customer->isLogged()) {
			$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
		} else {
			$data['customer_name'] = '';
		}

		$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
		$data['rating'] = (int)$product_info['rating'];

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
		} else {
			$data['captcha'] = '';
		}

		$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

		$data['products'] = array();

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

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			}

			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
			} else {
				$tax = false;
			}


			$data['products'][] = [
				'product_id' => $result['product_id'],
				'thumb' => $image,
				'name' => $result['name'],
				'thumb_swap' => $this->model_tool_image->resize($images, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
					$this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')),
				'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
				'price' => $price,
				'special' => $special,
				'tax' => $tax,
				'href' => $this->url->link('product/product', 'product_id=' . $result['product_id']),
				'quick' => $this->url->link('product/quick_view', '&product_id=' . $result['product_id'])
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

    $date = new DateTime();
    $date->add(new DateInterval('P1D'));
    $priceValidUntil = $date->format('Y-m-d');

    $microdata = [
      "@context" => "https://schema.org/",
      "@type" => "Product",
      "name" => $data['heading_title'],
      "image" => [ $data['images'][0] ],
      "description" => $data['description'],
      "sku" => $data['product_id'],
      "offers" => [
        "@type" => "Offer",
        "url" => $this->url->link('product/product', "product_id={$data['product_id']}"),
        "priceCurrency" => "UAH",
        "price" => $data['price_uah'],
        "priceValidUntil" => $priceValidUntil,
        "itemCondition" => "https://schema.org/NewCondition",
        "availability" => empty($data['productCount']) ? "https://schema.org/OutOfStock" : "https://schema.org/InStock"
      ]
    ];

    $this->document->setTitle($product_info['meta_title']);
		$this->document->setDescription($product_info['meta_description']);
    $this->document->setKeywords($product_info['meta_keyword']);
    $this->document->setMicrodata(json_encode($microdata));

    $data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('product/product', $data));
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
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
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

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
				$json['error'] = $this->language->get('error_rating');
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

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);

		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day' => $this->language->get('text_day'),
					'week' => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month' => $this->language->get('text_month'),
					'year' => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
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
				'name' => $this->language->get('text_main_stock'),
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
}
