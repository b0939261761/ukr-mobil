<?
class ControllerAccountOrder extends Controller {
  public function info() {
    $data['orderId'] = (int)($this->request->get['order_id'] ?? 0);

    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/order/info', ['order_id' => $data['orderId']]);
      $this->response->redirect($this->url->link('account/login'));
    }

    $order = $this->getOrder($data['orderId']);
    if (empty($order)) $this->response->redirect($this->url->link('error/not_found'));

    $data = array_merge($data, $order);
    $data['comment'] = nl2br($order['comment']);

    $total = 0;
    $data['products'] = $this->getOrderProducts($data['orderId']);
    foreach ($data['products'] as &$product) {
      $total += $product['total'];
      $product['priceFormat'] = $this->currency->format($product['price']);
      $product['totalFormat'] = $this->currency->format($product['total']);
    }

    $data['total'] = $this->currency->format( $total);
    $data['linkOrders'] = "{$this->url->link('account/account')}#orders";
    $data['headingH1'] = 'Детали заказа';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/order_info', $data));
  }

  private function getOrder($orderId) {
    $sql = "
      SELECT
        DATE_FORMAT(r.date_added, '%d.%m.%Y %T') AS dateAdded,
        r.ttn,
        r.ttn_status AS ttnStatus,
        r.payment_method AS paymentMethod,
        r.shipping_method AS shippingMethod,
        r.shipping_address_1 AS shippingAddress,
        r.comment,
        oos.name as statusName
      FROM oc_order r
      LEFT JOIN oc_order_status oos on oos.order_status_id = r.order_status_id
      WHERE r.order_id = $orderId
    ";
    return $this->db->query($sql)->row;
  }

  private function getOrderProducts($orderId) {
    $sql = "
      SELECT
        oop.product_id AS productId,
        opd.name,
        oop.price,
        oop.quantity,
        oop.total
      FROM oc_order_product oop
      LEFT JOIN oc_product_description opd ON opd.product_id = oop.product_id
      WHERE order_id = {$orderId} AND opd.language_id = 2
      ORDER BY opd.name
    ";

    return $this->db->query($sql)->rows;
  }
}
