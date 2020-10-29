<?php
use Ego\Controllers\BaseController;
use Ego\Models\Order;
use Ego\Models\OrderProduct;
use Ego\Models\OrderTotal;
use Ego\Models\Product;
use Ego\Models\ProductToStock;
use Ego\Providers\MailProvider;
use Ego\Providers\Util;
use Ego\Providers\Validator;

class ControllerCheckoutCart extends BaseController {

  public function index() {
    // Validate cart has products and has stock.
    if (!$this->cart->hasProducts() ||
      (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))
    ) {
      $this->response->redirect($this->url->link('checkout/empty_cart'));
    }

    // Validate minimum quantity requirements.
    $products = $this->cart->getProducts();

    foreach ($products as $product) {
      $product_total = 0;

      foreach ($products as $product_2) {
        if ($product_2['product_id'] == $product['product_id']) {
          $product_total += $product_2['quantity'];
        }
      }

      if ($product['minimum'] > $product_total) {
        $this->response->redirect($this->url->link('checkout/cart'));
      }
    }

    $data['headingH1'] = 'Оформление заказа';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $data['firstName'] = $this->customer->getFirstName();
    $data['lastName'] = $this->customer->getLastName();
    $data['phone'] = $this->customer->getTelephone();
    $data['email'] = $this->customer->getEmail();
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');

    $this->response->setOutput($this->load->view('checkout/cart', $data));
  }

  /**
   * Return products in cart
   */
  public function getCartProducts() {
    $success = false;
    $msg = self::MSG_INTERNAL_ERROR;
    $data = [];

    try {
      $data['products'] = $this->cart->getProducts();
      $data['currency'] = [
        'signLeft' => $this->currency->getSymbolLeft($this->session->data['currency']),
        'signRight' => $this->currency->getSymbolRight($this->session->data['currency'])
      ];
      $data['total'] = $this->currency->format($this->cart->getTotal(), $this->session->data['currency']);

      foreach ($data['products'] as &$product) {
        $product['price'] = $this->currency->format($product['price'], $this->session->data['currency']);
        $product['total'] = $this->currency->format($product['total'], $this->session->data['currency']);
        $product['href'] = $this->url->link('product/product', "product_id=" . $product['product_id']);
      }

      $success = true;
      $msg = self::MSG_SUCCESS;
    } catch (\Exception $ex) {
      $msg = $ex->getMessage();
    }

    $this->_prepareJson([
      'success' => $success,
      'msg' => $msg,
      'data' => $data
    ]);
  }

  /**
   * Remove item from cart
   */
  public function removeItem() {
    $success = false;
    $msg = self::MSG_INTERNAL_ERROR;
    $data = [];

    try {
      //region Input Data
      $transferData = $this->getInput('transferData');
      //endregion

      //region Check required fields is not empty
      if (($errorField = Validator::checkRequiredFields([
        'cartId'
      ], $transferData))) {
        $description = Util::getArrItem($errorField, 'description', '');

        throw new \RuntimeException("Field '{$description}' must be filled.");
      }
      //endregion

      $cartId = (int)Util::getArrItem($transferData, 'cartId');

      //  Update product quantity
      $this->cart->remove($cartId);

      $success = true;
      $msg = self::MSG_SUCCESS;
    } catch (\Exception $ex) {
      $msg = $ex->getMessage();
    }

    $this->_prepareJson([
      'success' => $success,
      'msg' => $msg,
      'data' => $data
    ]);
  }

  public function changeProductQuantity() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $cartId = (int)$requestData['cartId'] ?? 0;
    $quantity = (int)$requestData['quantity'] ?? 0;

    $productToStock = new \Ego\Models\ProductToStock();
    $productId = null;

    foreach ($this->cart->getProducts() as $product) {
      if ((int)$product['cart_id'] === $cartId) {
        $productId = (int)$product['product_id'];
        break;
      }
    }

    $enoughQuantity = false;
    if ($quantity <= $productToStock->getCount($productId)) {
      $this->cart->update($cartId, $quantity);
      $enoughQuantity = true;
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode([ 'enoughQuantity' => $enoughQuantity ]));
  }


  public function doOrder() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $baseModel = new \Ego\Models\BaseModel();

    try {
      $baseModel->_getDb()->beginTransaction();

      $orderModel = new Order();
      $orderProductModel = new OrderProduct();
      $orderTotalModel = new OrderTotal();

      $recipientFirstName = $requestData['firstName'] ?? '';
      $recipientLastName = $requestData['lastName'] ?? '';
      $recipientPhone = $requestData['phone'] ?? '';
      $recipientEmail = $requestData['email'] ?? '';
      $shippingMethod = $requestData['shippingMethod'] ?? '';
      $shippingAddress = $requestData['shippingAddress'] ?? '';
      $paymentMethod = $requestData['paymentMethod'] ?? '';
      $isValidPhone = $requestData['isValidPhone'] ?? 0;
      $isValidEmail = $requestData['isValidEmail'] ?? 0;

      $firstName = $recipientFirstName;
      $lastName = $recipientLastName;
      $phone = $recipientPhone;

      if ($this->customer->isLogged()) {
        $firstName = $this->customer->getFirstName();
        $lastName = $this->customer->getLastName();
        $phone = $this->customer->getTelephone();
      }

      $_productStocks = [];

      foreach ($this->cart->getProducts() as $product) {
        $_productStocks[(int)$product['product_id']] = [
          'product_id' => (int)$product['product_id'],
          'quantity' => (int)$product['quantity']
        ];
      }

      $productsStock = $this->getStocks($_productStocks);
      $productIdsStock = [];

      //  Convert products stock in IDs array
      foreach ($productsStock as $productStock) {
        foreach ($productStock as $product) $productIdsStock[] = (int)$product['product_id'];
      }

			$orderList = [];

      //  Get order list with stocks and products
      foreach ($this->cart->getProducts() as $product) {
        $productId = (int)$product['product_id'];
        $productQuantity = (int)$product['quantity'];

        $skip = false;

        //  Check if product quantity fully contain on single stock
        foreach ($productsStock as $productStock) {
          foreach ($productStock as $_product) {
            if ($_product['product_id'] !== $productId) continue;

            if (!isset($orderList[$_product['stock_id']])) $orderList[$_product['stock_id']] = [];

            $orderList[$_product['stock_id']][] = array_merge($product, [
              'quantity' => $productQuantity
            ]);

            $skip = true;
            break;
          }

          if ($skip) break;
        }

        if ($skip) continue;

        //  If product quantity don't fully contain on single stock let's split it
        foreach ($this->getStocksByQuantity($productId, $productQuantity) as $stock) {
          if (!isset($orderList[$stock->getStockId()])) $orderList[$stock->getStockId()] = [];

          $orderList[$stock->getStockId()][] = array_merge($product, [
            'quantity' => $stock->getQuantity()
          ]);
        }
      }

			$cartTotal = 0;
			$orderIds = [];

      foreach ($orderList as $stockId => $order) {
        $total = 0;

        foreach ($order as $product) $total += $product['price'] * $product['quantity'];
        $cartTotal += $total;

        $orderRow = (new \Ego\Struct\OrderRowStruct())
          ->setCustomerId(empty($this->customer->getId()) ? 0 : $this->customer->getId())
          ->setCustomerGroupId(empty($this->customer->getGroupId()) ? 0 : $this->customer->getGroupId())
          ->setFirstName($firstName)
          ->setLastName($lastName)
          ->setEmail($recipientEmail)
          ->setTelephone($recipientPhone)
          ->setComment($requestData['comment'] ?? '')
          ->setPaymentMethod($paymentMethod)
          ->setShippingFirstName($recipientFirstName)
          ->setShippingLastName($recipientLastName)
          ->setShippingTelephone($recipientPhone)
          ->setShippingAddress1($shippingAddress)
          ->setShippingMethod($shippingMethod)
          ->setTotal($total)
          ->setStockId($stockId);

        $orderId = $orderModel->add($orderRow);

        if ($orderId < 0) throw new Exception('Error occurred while create order');

        $orderIds[] = $orderId;

        $orderTotalRow = (new \Ego\Struct\OrderTotalRowStruct())
          ->setOrderId($orderId)
          ->setValue($total);

        if ($orderTotalModel->add($orderTotalRow) < 0) throw new Exception('Error occurred while create order');

        foreach ($order as $product) {
          $productRow = (new \Ego\Struct\OrderProductRowStruct())
            ->setOrderId($orderId)
            ->setProductId($product['product_id'])
            ->setPrice($product['price'])
            ->setQuantity($product['quantity'])
            ->setTotal($product['price'] * $product['quantity']);

          $orderProductModel->add($productRow);
        }
      }

      $configService = new \Ego\Services\ConfigService();
      $emails = $configService->getEmailAdministrator();
      if ($isValidEmail) $emails[] = $recipientEmail;

      (new MailProvider())
        ->setTo($emails)
        ->setFrom($configService->getEmailAdministratorMain(), $configService->getSiteTitle())
        ->setSubject('Новый Заказ')
        ->setView('mails.new-order')
        ->setBodyData([
          'header-title' => 'Новый Заказ',
          'orderId' => implode(', ', $orderIds),
          'customerName' => "{$firstName} {$lastName}",
          'aboutDelivery' => $shippingMethod,
          'recipientName' => "{$recipientFirstName} {$recipientLastName}",
          'recipientPhone' => $recipientPhone,
          'shippingAddress' => $shippingAddress,
          'productList' => $this->getOrderProducts($orderIds),
          'total' => $this->currency->format($cartTotal)
        ])
        ->sendMail();

      if ($isValidPhone && $isValidEmail) $this->cart->clear();
      $baseModel->_getDb()->commit();
      $this->response->setOutput($this->url->link('checkout/success', ['orders' => $orderIds]));
    } catch (Exception $ex) {
      $baseModel->_getDb()->rollBack();
      $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 400 Bad Request');
      $this->response->setOutput($ex->getMessage());
    }
  }

  private function getOrderProducts($orders) {
    if (empty($orders)) return [];
    $sqlOrders = implode(',', $orders);

    $sql = "
      SELECT
        oop.product_id AS sku,
        opd.name,
        price,
        SUM(quantity) AS quantity,
        SUM(total) AS total
      FROM oc_order_product oop
      LEFT JOIN oc_product_description opd ON opd.product_id = oop.product_id
      WHERE order_id IN ({$sqlOrders})
        AND opd.language_id = 2
			GROUP BY oop.product_id
    ";

    $products = $this->db->query($sql)->rows;

    foreach ($products as &$product) {
      $product['priceFormat'] = $this->currency->format($product['price']);
      $product['totalFormat'] = $this->currency->format($product['total']);
    }

    return $products;
  }

  public function checkEnoughQuantity() {
    $_productStocks = [];

    foreach ($this->cart->getProducts() as $product) {
      $_productStocks[(int)$product['product_id']] = [
        'product_id' => (int)$product['product_id'],
        'quantity' => (int)$product['quantity']
      ];
    }

    $productsStock = $this->getStocks($_productStocks);
    $productIdsStock = [];

    //  Convert products stock in IDs array
    foreach ($productsStock as $productStock) {
      foreach ($productStock as $product) {
        $productIdsStock[] = (int)$product['product_id'];
      }
    }

    $orderList = [];

    //  Get order list with stocks and products
    foreach ($this->cart->getProducts() as $product) {
      $productId = (int)$product['product_id'];
      $productQuantity = (int)$product['quantity'];

      $skip = false;

      //  Check if product quantity fully contain on single stock
      foreach ($productsStock as $productStock) {
        foreach ($productStock as $_product) {
          if ($_product['product_id'] !== $productId) {
            continue;
          }

          if (!isset($orderList[$_product['stock_id']])) {
            $orderList[$_product['stock_id']] = [];
          }

          $orderList[$_product['stock_id']][] = array_merge($product, [
            'quantity' => $productQuantity
          ]);

          $skip = true;
          break;
        }

        if ($skip) break;
      }

      if ($skip) continue;

      //  If product quantity don't fully contain on single stock let's split it
      foreach ($this->getStocksByQuantity($productId, $productQuantity) as $stock) {
        if (!isset($orderList[$stock->getStockId()])) $orderList[$stock->getStockId()] = [];

        $orderList[$stock->getStockId()][] = array_merge($product, [
          'quantity' => $stock->getQuantity()
        ]);
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode([ 'enoughQuantity' => count($orderList) === 1 ]));
  }

  public function checkEnoughQuantityForPickup() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $shippingMethod = $requestData['shippingMethod'] ?? '';

    $productModel = new Product();
    $productToStockModel = new ProductToStock();

    $products = $this->cart->getProducts();

    if (empty($products)) $products = [];
    $enoughQuantity = true;

    foreach ($products as $product) {
      $product_id = $product['product_id'];
      $quantity = $product['quantity'];

      switch ($shippingMethod) {
        case 'Самовывоз из г. Черновцы':
          $productRow = $productModel->get($product_id, true);

          if ($productRow->getQuantity() < $quantity) {
            $enoughQuantity = false;
            break;
          }

          break;

        case 'Самовывоз из г. Ровно':
          if ($productToStockModel->getCountByProductAndStock($product_id, 2) < $quantity) {
            $enoughQuantity = false;
          }

          break;
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode([ 'enoughQuantity' => $enoughQuantity ]));
  }

  public function add() {
    $json = array();

    if (isset($this->request->post['product_id'])) {
      $product_id = (int)$this->request->post['product_id'];
    } else {
      $product_id = 0;
    }

    $this->load->model('catalog/product');

    //region Define Models
    $productToStockModel = new ProductToStock();
    //endregion

    $product_info = $this->model_catalog_product->getProduct($product_id);

    if ($product_info) {
      if (isset($this->request->post['quantity'])) {
        $quantity = (int)$this->request->post['quantity'];
      } else {
        $quantity = 1;
      }

      //  Check available product count on stocks
      $productQuantity = 0;

      foreach ($this->cart->getProducts() as $product) {
        if ($product_id != $product['product_id']) {
          continue;
        }

        $productQuantity = (int)$product['quantity'];
      }

      if ($productQuantity + $quantity > $productToStockModel->getCount($product_id)) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
          'error' => 'Недостаточно товаров на складе',
          'total' => $this->cart->countProducts()
        ]));

        return;
      }

      if (isset($this->request->post['option'])) {
        $option = array_filter($this->request->post['option']);
      } else {
        $option = array();
      }

      $product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

      foreach ($product_options as $product_option) {
        if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
          $json['error']['option'][$product_option['product_option_id']] = sprintf(
            $this->language->get('error_required'), $product_option['name']
          );
        }
      }

      if (isset($this->request->post['recurring_id'])) {
        $recurring_id = $this->request->post['recurring_id'];
      } else {
        $recurring_id = 0;
      }

      $recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

      if ($recurrings) {
        $recurring_ids = array();

        foreach ($recurrings as $recurring) {
          $recurring_ids[] = $recurring['recurring_id'];
        }

        if (!in_array($recurring_id, $recurring_ids)) {
          $json['error']['recurring'] = $this->language->get('error_recurring_required');
        }
      }

      if (!$json) {
        $this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);

        $json['success'] = sprintf(
          'Успех: вы добавили <a href="%s">%s</a> в вашу <a href="%s">корзину</a>!',
          $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']),
          $product_info['name'], $this->url->link('checkout/cart')
        );

        // Unset all shipping and payment methods
        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_methods']);

        // Totals
        $this->load->model('setting/extension');

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
          'totals' => &$totals,
          'taxes' => &$taxes,
          'total' => &$total
        );

        // Display prices
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
          $sort_order = array();

          $results = $this->model_setting_extension->getExtensions('total');

          foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
          }

          array_multisort($sort_order, SORT_ASC, $results);

          foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
              $this->load->model('extension/total/' . $result['code']);

              // We have to put the totals in an array so that they pass by reference.
              $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
          }

          $sort_order = array();

          foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
          }

          array_multisort($sort_order, SORT_ASC, $totals);
        }

        $json['total'] = $this->cart->countProducts();
      } else {
        $json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function edit() {
    $json = array();

    // Update
    if (!empty($this->request->post['quantity'])) {
      foreach ($this->request->post['quantity'] as $key => $value) {
        $this->cart->update($key, $value);
      }

      $this->session->data['success'] = 'Успех: вы изменили свою корзину покупок!';

      unset($this->session->data['shipping_method']);
      unset($this->session->data['shipping_methods']);
      unset($this->session->data['payment_method']);
      unset($this->session->data['payment_methods']);
      unset($this->session->data['reward']);

      $this->response->redirect($this->url->link('checkout/cart'));
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function remove() {
    $json = array();

    // Remove
    if (isset($this->request->post['key'])) {
      $this->cart->remove($this->request->post['key']);

      unset($this->session->data['vouchers'][$this->request->post['key']]);

      $json['success'] = 'Успех: вы изменили свою корзину покупок!';

      unset($this->session->data['shipping_method']);
      unset($this->session->data['shipping_methods']);
      unset($this->session->data['payment_method']);
      unset($this->session->data['payment_methods']);
      unset($this->session->data['reward']);

      // Totals
      $this->load->model('setting/extension');

      $totals = array();
      $taxes = $this->cart->getTaxes();
      $total = 0;

      // Because __call can not keep var references so we put them into an array.
      $total_data = array(
        'totals' => &$totals,
        'taxes' => &$taxes,
        'total' => &$total
      );

      // Display prices
      if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
        $sort_order = array();

        $results = $this->model_setting_extension->getExtensions('total');

        foreach ($results as $key => $value) {
          $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
          if ($this->config->get('total_' . $result['code'] . '_status')) {
            $this->load->model('extension/total/' . $result['code']);

            // We have to put the totals in an array so that they pass by reference.
            $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
          }
        }

        $sort_order = array();

        foreach ($totals as $key => $value) {
          $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $totals);
      }

      $json['total'] = $this->cart->countProducts();
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  private function getStocks(array $products) {
    if (empty($products)) {
      return [];
    }

    //region Define Models
    $productToStockModel = new \Ego\Models\ProductToStock();
    //endregion

    $quantityList = [];
    $productIds = [];

    foreach ($products as $product) {
      $quantityList[] = (int)$product['quantity'];
      $productIds[(int)$product['product_id']] = (int)$product['product_id'];
    }

    arsort($quantityList);
    $quantityList = array_unique($quantityList);

    $productsStock = [];
    $maxProducts = 0;
    $bestProductStocks = null;

    foreach ($quantityList as $quantity) {
      //  Get main stock
      $mainProductStocks = $productToStockModel->getStockWithAvailableProductsQuantityMainStock($productIds, $quantity);
      $mainProductCount = (int)Util::getArrItem($mainProductStocks, 'cnt', 0);
      //  Get another stocks
      $anotherProductStocks = $productToStockModel->getStockWithAvailableProductsQuantity($productIds, $quantity);
      $anotherProductCount = (int)Util::getArrItem($anotherProductStocks, 'cnt', 0);

      //  Compare two stocks
      if ($mainProductCount > $anotherProductCount) {
        $productCount = $mainProductCount;
        $productStocks = $mainProductStocks;
      } else {
        $productCount = $mainProductCount;
        $productStocks = $anotherProductStocks;
      }

      //  Skip if not found product count in secondary stocks
      if (empty($productStocks)) {
        continue;
      }

      //  Remember best result
      if ($productCount > $maxProducts) {
        $maxProducts = $productCount;
        $bestProductStocks = $productStocks;
      }
    }

    if (empty($bestProductStocks)) {
      $products = [];
    } else {
      $stockId = (int)$bestProductStocks['stock_id'];
      $productsStock[$stockId] = [];

      foreach (explode(',', $bestProductStocks['products']) as $productId) {
        $productsStock[$stockId][] = [
          'product_id' => (int)$productId,
          'stock_id' => (int)$stockId
        ];

        unset($products[$productId]);
      }
    }

    //return $productsStock;
    return array_merge(
      $productsStock,
      $this->getStocks($products)
    );

  }

  /**
   * Return stock ID by allowed product quantity
   *
   * @param int $productId
   * @param int $quantity
   * @return bool|int
   */
  private function getStockByAllowedQuantity(int $productId, int $quantity) {
    //region Define Models
    $productModel = new Product();
    $productToStockModel = new \Ego\Models\ProductToStock();
    //endregion

    $quantityOnMainStock = $productModel->get($productId, true);
    $quantityOnMainStock = empty($quantityOnMainStock) ? 0 : $quantityOnMainStock->getQuantity();

    //  Enough quantity on main stock
    if ($quantityOnMainStock >= $quantity) {
      return 0;
    }

    $quantityOnStock = $productToStockModel->getWithAllowedQuantity($productId, $quantity, true);

    if (empty($quantityOnStock)) {
      return false;
    } else {
      return $quantityOnStock->getStockId();
    }
  }

  /**
   * Return stocks for order with allowed quantity
   *
   * @param int $productId
   * @param int $quantity
   * @return array|\Ego\Struct\ProductToStockRowStruct[]
   */
  private function getStocksByQuantity(int $productId, int $quantity) {
    //  Stock list
    /** @var \Ego\Struct\ProductToStockRowStruct[] $result */
    $result = [];

    //region Define Models
    $productModel = new Product();
    $productToStockModel = new \Ego\Models\ProductToStock();
    //endregion

    //  Try find needed product quantity on single stock
    if (($singleStockId = $this->getStockByAllowedQuantity($productId, $quantity)) !== false) {
      return [
        (new \Ego\Struct\ProductToStockRowStruct())
          ->setProductId($productId)
          ->setStockId($singleStockId)
          ->setQuantity($quantity)
      ];
    }

    //  Main stock
    $quantityOnMainStock = $productModel->get($productId, true);
    $quantityOnMainStock = empty($quantityOnMainStock) ? 0 : $quantityOnMainStock->getQuantity();

    if ($quantityOnMainStock > 0) {
      $quantity -= $quantityOnMainStock;

      $result[] = (new \Ego\Struct\ProductToStockRowStruct())
        ->setProductId($productId)
        ->setStockId(0)
        ->setQuantity($quantityOnMainStock);
    }

    //  Additional stocks
    $stockList = $productToStockModel->getListByProduct($productId, true);
    $stockList = empty($stockList) ? [] : $stockList;

    foreach ($stockList as $stock) {
      if ($stock->getQuantity() >= $quantity) {
        $result[] = $stock->setQuantity($quantity);

        break;
      } else {
        $quantity -= $stock->getQuantity();
        $result[] = $stock;
      }
    }

    //  If empty stock
    if (empty($result)) {
      $result[] = (new \Ego\Struct\ProductToStockRowStruct())
        ->setProductId($productId)
        ->setStockId(0)
        ->setQuantity($quantityOnMainStock);
    }

    return $result;
  }

}
