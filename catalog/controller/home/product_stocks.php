<?
use Ego\Models\Stocks;
use Ego\Providers\Util;

class ControllerHomeProductStocks extends \Ego\Controllers\BaseController {
  public function index() {
    $this->load->model('catalog/product');

    $egoProductModel = new \Ego\Models\Product();
    $egoProductDescriptionModel = new \Ego\Models\ProductDescription();
    $egoProductToStockModel = new \Ego\Models\ProductToStock();
    $stocksModel = new Stocks();

    $data['productList'] = [];
    $productList = $egoProductModel->getStocks(9, (int)$this->config->get('config_customer_group_id'), true);
    $productList = empty($productList) ? [] : $productList;

    foreach ($productList as $productSpecial) {
      $product = $egoProductModel->get($productSpecial->getProductId(), true);

      if (empty($product)) continue;

      $stockList = [
        [
          'name' => 'г. Черновцы',
          'quantity' => $product->getQuantity()
        ]
      ];

      $productToStockList = $egoProductToStockModel->getListByProduct($product->getProductId(), true) ?? [];

      foreach ($productToStockList as $productToStock) {
        $stockRow = $stocksModel->get($productToStock->getStockId(), true);

        if (empty($stockRow)) continue;

        $stockList[] = [
          'name' => $stockRow->getName(),
          'quantity' => $productToStock->getQuantity()
        ];
      }

      $description = $egoProductDescriptionModel->get($product->getProductId(), 2, true);

      //  Price
      $product_info = $this->model_catalog_product->getProduct($product->getProductId());
      $price = Util::strToFloat($product_info['price']);
      $priceSpecial = Util::strToFloat($product_info['special']);

      //  Product image
      if (empty($product->getImage())) {
        $productImage = $image = $this->model_tool_image->resize(
          'placeholder.png',
          $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
          $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
        );
      } else {
        $productImage = '/image/' . $product->getImage();
      }

      $data['productList'][] = [
        'link' => $this->url->link('product/product', 'product_id=' . $product->getProductId()),
        'image' => $productImage,
        'name' => empty($description) ? '' : $description->getName(),
        'price' => $this->currency->format($price),
        'special' => $this->currency->format($priceSpecial),
        'sku' => $product->getSku(),
        'productCount' => $this->getProductCount($product->getProductId()),
        'stockList' => $stockList
      ];
    }

    return $this->load->view('home/product_stocks', $data);
  }

  private function getProductCount($productId) {
    $productId = (int)$productId;
    $egoProductToStockModel = new \Ego\Models\ProductToStock();
    return $egoProductToStockModel->getCount($productId);
  }
}