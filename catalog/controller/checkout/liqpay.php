<?
class ControllerCheckoutLiqpay extends Controller {
  public function index() {
    $data['headingH1'] = 'Оплата картой онлайн (LiqPay)';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['linkHome'] = $this->url->link('common/home');

    $orderIds = [1];
    $linkSuccess = $this->url->link('checkout/success', ['orders' => $orderIds]);
    $data['liqpayPayload'] = $this->getLiqpayData(1, $orderIds, $linkSuccess);

    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('checkout/liqpay', $data));
  }


  private function getLiqpayData($totalsUAH, $orderIds, $linkSuccess) {
    $data = base64_encode(json_encode([
      'public_key'  => LIQPAY_PUBLIC_KEY,
      'action'      => 'pay',
      'language'    => 'ru',
      'amount'      => $totalsUAH,
      'currency'    => 'UAH',
      'description' => "Оплата за товар по замовленню № " . implode(', ', $orderIds),
      'order_id'    => implode(',', $orderIds),
      'version'     => 3,
      'result_url'  => $linkSuccess,
      'server_url'  => $this->url->link('checkout/cart/liqpayResponse')
    ]));

    return [
      'data'      => $data,
      'signature' => $this->getSignature($data)
    ];
  }

  private function getSignature($data) {
    return base64_encode(sha1(LIQPAY_PRIVATE_KEY . $data . LIQPAY_PRIVATE_KEY, 1));
  }

  public function checkSuccess() {
    $orders = $this->request->get['orders'] ?? [];
    $result = 0;

    if (count($orders)){
      $orderIds = $this->db->escape(implode(', ', $orders));
      $sql = "SELECT MIN(hasPayment) AS hasPayment FROM oc_order WHERE order_id IN ({$orderIds})";
      $result = $this->db->query($sql)->row['hasPayment'] ?? 0;
    }

    $this->response->setOutput($result);
  }
}
