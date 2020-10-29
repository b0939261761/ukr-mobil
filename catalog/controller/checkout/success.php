<?
class ControllerCheckoutSuccess extends Controller {
  public function index() {
    $orders = $this->request->get['orders'];
    $products = $this->getOrderProducts($orders);

    $transactionId = implode('-', $orders);
    $transactionTotal = array_reduce(
      $products,
      function ($prev, $cur) { return $prev + $cur['price'] * $cur['quantity']; },
      0
    );

    $productList = json_encode($products);

    $dataLayer = "
      dataLayer = [{
        transactionId: '{$transactionId}',
        transactionAffiliation: 'UKRMobil',
        transactionTotal: {$transactionTotal},
        transactionProducts: {$productList},
        event: 'trackTrans'
      }];
    ";

    $this->document->setDataLayer($dataLayer);
    $data['headingH1'] = 'Успешная покупка';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('checkout/success', $data));
  }

  private function getOrderProducts($orders) {
    if (empty($orders)) return [];
    $sqlOrders = implode(',', $orders);

    $sql = "
      SELECT
        oop.product_id AS sku,
        opd.name,
        price,
        SUM(quantity) AS quantity
      FROM oc_order_product oop
      LEFT JOIN oc_product_description opd ON opd.product_id = oop.product_id
      WHERE order_id IN ({$sqlOrders})
        AND opd.language_id = 2
			GROUP BY oop.product_id
    ";
    return $this->db->query($sql)->rows;
  }
}
