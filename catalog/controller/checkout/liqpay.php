<?
class ControllerCheckoutLiqpay extends Controller {
  public function index() {
    $orderIds = $this->request->get['orders'];
    $ordersSQL = $this->db->escape(implode(', ', $orderIds));

    $linkSuccess = $this->url->link('checkout/success', ['orders' => $orderIds]);
    if ($this->hasPayment($ordersSQL)) return $this->response->redirect($linkSuccess);

    $sql = "
      SELECT ROUND(SUM(o.total) * c.value) AS totalsUAH
      FROM oc_order o
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE o.order_id IN ({$ordersSQL})
    ";

    $totalsUAH = $this->db->query($sql)->row['totalsUAH'];

    $data['headingH1'] = 'Оплата картой онлайн (LiqPay)';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $data['liqpayPayload'] = $this->getLiqpayData($totalsUAH, $orderIds, $linkSuccess);
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('checkout/liqpay', $data));
  }

  public function callback() {
    $request = $this->request->post;
    $reqData = $request['data'] ?? '';
    $reqSignature = $request['signature'] ?? '';

    if ($reqSignature !== $this->getSignature($reqData)) return;
    $data = json_decode(base64_decode($reqData), true);

    if ($data['status'] !== 'success') return;
    $sql = "UPDATE oc_order SET hasPayment = 1 WHERE order_id IN ({$data['order_id']})";
    $this->db->query($sql)->row;
  }

  public function checkSuccess() {
    $orders = $this->request->get['orders'];
    $ordersSQL = $this->db->escape(implode(', ', $orders));
    $this->response->setOutput($this->hasPayment($ordersSQL));
  }

  private function hasPayment($ordersSQL) {
    $sql = "SELECT MIN(hasPayment) AS hasPayment FROM oc_order WHERE order_id IN ({$ordersSQL})";
    return $this->db->query($sql)->row['hasPayment'] ?? 0;
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
      'server_url'  => $this->url->link('checkout/liqpay/callback')
    ]));

    return [
      'data'      => $data,
      'signature' => $this->getSignature($data)
    ];
  }

  private function getSignature($data) {
    return base64_encode(sha1(LIQPAY_PRIVATE_KEY . $data . LIQPAY_PRIVATE_KEY, 1));
  }
}
