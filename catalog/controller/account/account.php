<?
class ControllerAccountAccount extends Controller {
  public function index() {
    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/account');
      $this->response->redirect($this->url->link('account/login'));
    }

    $data['linkLogout'] = $this->url->link('account/logout');

    $customerId = (int)$this->customer->getId();

    $sql = "
      SELECT
        firstname AS firstName,
        lastname AS lastName,
        email,
        telephone AS phone,
        region,
        city,
        warehouse,
        type_price AS typePrice,
        credit_limit AS creditLimit,
        payment_delay AS paymentDelay,
        manager_fullname AS managerFullName
      FROM oc_customer
      WHERE customer_id = {$customerId}
    ";
    $data['customer'] = $this->db->query($sql)->row;

    $sql = "
      SELECT
        o.order_id AS orderId,
        DATE_FORMAT(o.date_added, '%d.%m.%y') AS date,
        o.payment_method AS paymentMethod,
        o.shipping_method AS shippingMethod,
        CONCAT(o.shipping_firstname, ' ', o.shipping_lastname) AS shippingFullName,
        o.ttn,
        o.ttn_status AS ttnStatus,
        s.name AS storeName,
        os.name AS orderStatusName,
        o.total AS totalUSD,
        ROUND(o.total * c.value) AS totalUAH
       FROM oc_order o
       LEFT JOIN store s ON s.id = o.stock_id
       LEFT JOIN oc_order_status os ON os.order_status_id = o.order_status_id
       LEFT JOIN oc_currency c ON c.currency_id = 980
       WHERE customer_id = {$customerId}
       ORDER BY order_id DESC
    ";
    $data['products'] = $this->db->query($sql)->rows;

    foreach ($data['products'] as &$item) {
      $item['link'] = $this->url->link('account/order', ['order_id' => $item['orderId']]);
      $item['linkDownload'] = $this->url->link('account/order/download', ['id' => $item['orderId']]);
    }

    // ---------------------------------------------

    $sql = "
      SELECT epc.epc_content AS content
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) = 'terms_of_sale' AND epc.epc_language = 2
      LIMIT 1
    ";
    $data['terms_of_sale'] = $this->db->query($sql)->row['content'] ?? '';

    // ---------------------------------------------

    $date = date_create();
    date_modify($date, '-3 month');
    $data['balanceDateFrom'] = date_format($date, 'd.m.Y');
    $data['balanceDateTo'] = date('d.m.Y');

    $data['headingH1'] = 'Личный кабинет';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/account', $data));
  }

  public function save() {
    $requestData = json_decode(file_get_contents('php://input'), true);

    $firstName = $this->db->escape($requestData['firstName'] ?? '');
    $lastName = $this->db->escape($requestData['lastName'] ?? '');
    $phone = $this->db->escape($requestData['phone'] ?? '');
    $email = $this->db->escape($requestData['email'] ?? '');
    $region = $this->db->escape($requestData['region'] ?? '');
    $city = $this->db->escape($requestData['city'] ?? '');
    $warehouse = $this->db->escape($requestData['warehouse'] ?? '');
    $password = $this->db->escape($requestData['password'] ?? '');

    $sqlPassword = '';
    if (!empty($password)) {
      $salt = token(9);
      $sqlPassword = ",
        password = SHA1(CONCAT('{$salt}', SHA1(CONCAT('{$salt}', SHA1('{$password}'))))),
        salt     = '{$salt}'";
    }

    $customerId = (int)$this->customer->getId();
    $sql = "
      UPDATE oc_customer SET
        firstname = '{$firstName}',
        lastname  = '{$lastName}',
        email     = '{$email}',
        telephone = '{$phone}',
        region    = '{$region}',
        city      = '{$city}',
        warehouse = '{$warehouse}',
        updated   = 1
        {$sqlPassword}
      WHERE customer_id = {$customerId}
    ";
    $this->db->query($sql);
    $this->response->setOutput('');
  }

  public function balance() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $dateFrom = $requestData['dateFrom'];
    $dateTo = $requestData['dateTo'];
    $customerId = (int)$this->customer->getId();
    $query = ['id' => $customerId, 'startdate' => $dateFrom, 'enddate' => $dateTo];
    $url = 'http://API:1@um.reality.sh/pavel_ut/hs/get_data/dtkt/' . http_build_query($query);

    $data = [];
    try {
      $options = stream_context_create(['http'=> ['timeout' => 3]]);
      foreach (json_decode(file_get_contents($url, false, $options), true) as $item) {
        $isTotal = $item['TotalString'] ?? false;
        $data[] = [
          'name'    => $item['Document'],
          'total'   => $isTotal ? '' : "{$item['DocumentSum']} ${item['DocumentCurrency']}",
          'balance' => "{$item['Balance']} ${item['BalanceCurrency']}",
          'url'     => $isTotal ? '' : $item['url']
        ];
      }
    } catch (Exception $e) {
      echo "${$e->getMessage()} URI: <code>{$url}</code> <br>";
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($data));
  }
}
