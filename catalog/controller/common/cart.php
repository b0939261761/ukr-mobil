<?php
class ControllerCommonCart extends Controller {
  public function index() {
    $this->load->language('common/cart');
    $this->load->model('tool/image');
    $configTheme = $this->config->get('config_theme');
    $imageWidth = $this->config->get("theme_{$configTheme}_image_cart_width");
    $imageHeight = $this->config->get("theme_{$configTheme}_image_cart_height");

    $totalAll = 0;
    $quantityAll = 0;

    foreach ($this->cart->getProducts() as $product) {
      $image = $product['image'] ? $product['image'] : 'placeholder.png';
      $total = $product['price'] * $product['quantity'];
      $quantityAll += $product['quantity'];
      $totalAll += $total;

      $data['products'][] = [
        'cart_id'   => $product['cart_id'],
        'thumb'     => $this->model_tool_image->resize($image, $imageWidth, $imageHeight),
        'name'      => $product['name'],
        'quantity'  => $product['quantity'],
        'price'     => $this->currency->format($product['price'], $this->session->data['currency']),
        'total'     => $this->currency->format($total, $this->session->data['currency']),
        'href'      => $this->url->link('product/product', ['product_id' => $product['product_id']])
      ];
    }

    $data['total'] = [
      'title' => 'Сумма (грн/$)',
      'quantity' => $quantityAll,
      'sum' => $this->currency->format($totalAll, $this->session->data['currency'])
    ];

    $data['cart'] = $this->url->link('checkout/cart');
    return $this->load->view('common/cart', $data);
  }

  public function info() {
    $this->response->setOutput($this->index());
  }
}
