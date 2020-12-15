<?
class ControllerAccountOrder extends Controller {
  public function index() {
    $data['orderId'] = (int)($this->request->get['order_id'] ?? 0);

    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/order', ['order_id' => $data['orderId']]);
      $this->response->redirect($this->url->link('account/login'));
    }

    $data['order'] = $this->getOrder($data['orderId']);
    if (empty($data['order'])) $this->response->redirect($this->url->link('error/not_found'));

    $data['order']['comment'] = nl2br($data['order']['comment']);
    $data['products'] = $this->getOrderProducts($data['orderId']);
    $data['headingH1'] = 'Детали заказа';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $data['breadcrumb'] = [
      'name' => 'Заказы',
      'link' => "{$this->url->link('account/account')}#orders"
    ];
    $this->document->setMicrodataBreadcrumbs([$data['breadcrumb']]);

    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/order', $data));
  }

  private function getOrder($orderId) {
    $customerId = (int)$this->customer->getId();
    $sql = "
      SELECT
        DATE_FORMAT(o.date_added, '%d.%m.%Y %T') AS dateAdded,
        CONCAT(o.shipping_firstname, ' ', o.shipping_lastname) AS shippingFullName,
        email,
        shipping_telephone AS shippingPhone,
        o.ttn,
        o.ttn_status AS ttnStatus,
        o.payment_method AS paymentMethod,
        o.shipping_method AS shippingMethod,
        o.shipping_address_1 AS shippingAddress,
        o.comment,
        os.name as statusName,
        o.total AS totalUSD,
        ROUND(o.total * c.value) AS totalUAH,
        o.commission_uah AS commissionUAH,
        ROUND(o.total + o.commission_uah / c.value , 2) AS toPayUSD,
        ROUND(o.total * c.value + o.commission_uah) AS toPayUAH
      FROM oc_order o
      LEFT JOIN oc_order_status os on os.order_status_id = o.order_status_id
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE o.order_id = {$orderId} AND o.customer_id = {$customerId}
    ";
    return $this->db->query($sql)->row;
  }

  private function getOrderProducts($orderId) {
    $sql = "
      SELECT
        op.product_id AS productId,
        pd.name,
        op.price AS priceUSD,
        ROUND(op.price * c.value) AS priceUAH,
        op.quantity AS quantity,
        total AS totalUSD,
        ROUND(total * c.value) AS totalUAH
      FROM oc_order_product op
      LEFT JOIN oc_product_description pd ON pd.product_id = op.product_id
      LEFT JOIN oc_currency c ON c.currency_id = 980
      WHERE op.order_id = {$orderId} AND pd.language_id = 2
      ORDER BY pd.name
    ";
    return $this->db->query($sql)->rows;
  }
}
