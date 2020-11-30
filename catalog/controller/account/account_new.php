<?
use Ego\Controllers\BaseController;
use Ego\Models\BaseModel;
use Ego\Models\Customer;
use Ego\Models\Order;
use Ego\Providers\Util;
use Ego\Providers\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ControllerAccountAccount extends BaseController {
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

    // $balanceDateFrom = date_create();
    // date_modify($balanceDateFrom, '-3 month');
    // $data['balanceDateFrom'] = date_format($balanceDateFrom, 'd.m.Y');
    // $data['balanceDateTo'] = date('d.m.Y');

    // $historyDateFrom = date_create();
    // date_modify($historyDateFrom, '-1 month');
    // $data['historyDateFrom'] = date_format($historyDateFrom, 'd.m.Y');
    // $data['historyDateTo'] = date('d.m.Y');

    $dateFrom = date_create();
    date_modify($dateFrom, '-1 month');
    $data['dateFrom'] = date_format($dateFrom, 'd.m.Y');
    $data['dateTo'] = date('d.m.Y');

    $data['headingH1'] = 'Личный кабинет';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
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

  public function history() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $dateFrom = $requestData['dateFrom'];
    $dateTo = $requestData['dateTo'];
    $customerId = (int)$this->customer->getId();
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
    $products = $this->db->query($sql)->rows;

    foreach ($products as &$item) {
      $item['link'] = $this->url->link('account/order', ['order_id' => $item['orderId']]);
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($products));
  }

  public function download() {
    $success = false;
    $msg = self::MSG_INTERNAL_ERROR;
    $code = 500;
    $data = [];

    try {
      $orderId = (int)($this->request->get['orderId'] ?? 0);

      if ($orderId <= 0) {
        throw new \InvalidArgumentException('Invalid order ID');
      }

      $this->load->language('account/order');

      //region Define Models
      $orderModel = new Order();
      $productDescriptionModel = new \Ego\Models\ProductDescription();

      $this->load->model('account/order');
      //endregion

      //  Get order
      $order = $orderModel->get($orderId, true);

      //region Prepare data
      $excelData = [
        'color_1' => 'eeeeee',
        'fontSize_1' => 8,
        'fontSize_2' => 11,
        'orderInfo' => [
          'colName' => [
            'char' => 'A',
            'number' => 1
          ],
          'colValue' => [
            'char' => 'B',
            'number' => 2
          ]
        ],
        'products' => [
          'colName' => [
            'char' => 'A',
            'number' => 1
          ],
          'colModel' => [
            'char' => 'B',
            'number' => 2
          ],
          'colCount' => [
            'char' => 'C',
            'number' => 3
          ],
          'colPrice' => [
            'char' => 'D',
            'number' => 4
          ],
          'colTotal' => [
            'char' => 'E',
            'number' => 5
          ]
        ],
        'total' => [
          'colName' => [
            'char' => 'D',
            'number' => 4
          ],
          'colValue' => [
            'char' => 'E',
            'number' => 5
          ]
        ]
      ];
      //endregion

      //region Create Order Info EXCEL file
      // Create new Spreadsheet object
      $spreadsheet = new Spreadsheet();
      // Set document properties
      $spreadsheet->getProperties()
        ->setCreator($this->config->get('config_name'))
        ->setTitle('Order Info')
        ->setSubject('Order Info');

      $spreadsheet->setActiveSheetIndex(0);

      //region Column Width
      //  Name
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colName']['number'])
        ->setWidth(95);

      //  Model
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colModel']['number'])
        ->setWidth(20);

      //  Count
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colCount']['number'])
        ->setWidth(20);

      //  Price
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colPrice']['number'])
        ->setWidth(20);

      //  Total
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colTotal']['number'])
        ->setWidth(20);
      //endregion

      $languageId = (int)$this->config->get('config_language_id');
      $fileName = "order-info-{$order->getOrderId()}.xls";
      $iRow = 1;

      //region Contact Info
      //  First name
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('Имя')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getFirstName())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Last name
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('Фамилия')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getLastName())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Email
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('Email')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getEmail())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;
      //endregion

      //region Order Info
      //  Order ID
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_order_id'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getOrderId())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->getStyle()
        ->getNumberFormat()
        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

      $iRow++;

      //  Date added
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_date_added'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue(date('d.m.Y', strtotime($order->getDateAdded())))
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Status
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('Статус')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value

      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getOrderStatusName())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Payment type
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_payment_method'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getPaymentMethod())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Delivery type
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_shipping_method'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getShippingMethod())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  TTN
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('ТТН')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue((string)$order->getTtn() . ' ')
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->getStyle()
        ->getNumberFormat()
        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
      $iRow++;

      //  TTN Status
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_tracking'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value

      // Get TTN status
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getTtnStatus())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Shipping address
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_shipping_address'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getShippingAddress1())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);
      //endregion

      //region Products Header
      //  Name
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colName']['number'], $iRow)
        ->setValue($this->language->get('column_name'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Model
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colModel']['number'], $iRow)
        ->setValue($this->language->get('column_model'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Count
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colCount']['number'], $iRow)
        ->setValue($this->language->get('column_quantity'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Price
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colPrice']['number'], $iRow)
        ->setValue($this->language->get('column_price'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Total
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colTotal']['number'], $iRow)
        ->setValue($this->language->get('column_total'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);

      $iRow++;
      //endregion

      //region Products
      // Products
      $data['products'] = array();

      $products = $this->model_account_order->getOrderProducts($order->getOrderId());

      foreach ($products as $product) {
        //  Product description
        $productDescriptionRow = $productDescriptionModel->get((int)$product['product_id'], $languageId, true);

        //  Name
        $productName =  empty($productDescriptionRow) ? '' : $productDescriptionRow->getName();
        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colName']['number'], $iRow)
          ->setValue($productName)
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        //  Model
        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colModel']['number'], $iRow)
          ->setValue($product['model'])
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        //  Quantity
        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colCount']['number'], $iRow)
          ->setValue($product['quantity'])
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colPrice']['number'], $iRow)
          ->setValue($this->currency->format($product['price']))
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colTotal']['number'], $iRow)
          ->setValue($this->currency->format($product['total']))
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        $iRow++;
      }
      //endregion

      //region Total
      $totals = $this->model_account_order->getOrderTotals($order->getOrderId());
      $total = $this->currency->format(Util::getArrItem($totals, '0.value', 0));

      //  Total
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['total']['colName']['number'], $iRow)
        ->setValue('Сумма')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['total']['colValue']['number'], $iRow)
        ->setValue($total)
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;
      //endregion
      //endregion

      //region Save
      // Set active sheet index to the first sheet, so Excel opens this as the first sheet
      $spreadsheet->setActiveSheetIndex(0);

      $writer = IOFactory::createWriter($spreadsheet, 'Xls');
      $writer->save(DIR_DOWNLOAD . '/' . $fileName);

      $data = [];
      $data['downloadUrl'] = '/system/storage/download/' . $fileName;
      $data['fileName'] = $fileName;
      //endregion

      $success = true;
      $msg = self::MSG_SUCCESS;
      $code = 200;
    } catch (\Exception $ex) {
      $msg = $ex->getMessage();
      $code = $ex->getMessage();
      $data = [];
    }

    $this->response->addHeader("Content-Type: application/vnd.ms-excel");
    $this->response->addHeader("Content-disposition: attachment; filename={$fileName}");
    $file = file_get_contents($data['downloadUrl']);
    $this->response->setOutput($file);

    // return $this->_prepareJson([
    //   'success' => $success,
    //   'message' => $msg,
    //   'code' => $code,
    //   'data' => $data
    // ]);
  }

  public function downloadOrderInfo() {
    $success = false;
    $msg = self::MSG_INTERNAL_ERROR;
    $code = 500;
    $data = [];

    try {
      //region Input Data
      $transferData = $this->getInput('transferData');
      //endregion

      //  Order ID
      $orderId = (int)Util::getArrItem($transferData, 'orderId');

      if ($orderId <= 0) {
        throw new \InvalidArgumentException('Invalid order ID');
      }

      $this->load->language('account/order');

      //region Define Models
      $orderModel = new Order();
      $productDescriptionModel = new \Ego\Models\ProductDescription();

      $this->load->model('account/order');
      //endregion

      //  Get order
      $order = $orderModel->get($orderId, true);

      //region Prepare data
      $excelData = [
        'color_1' => 'eeeeee',
        'fontSize_1' => 8,
        'fontSize_2' => 11,
        'orderInfo' => [
          'colName' => [
            'char' => 'A',
            'number' => 1
          ],
          'colValue' => [
            'char' => 'B',
            'number' => 2
          ]
        ],
        'products' => [
          'colName' => [
            'char' => 'A',
            'number' => 1
          ],
          'colModel' => [
            'char' => 'B',
            'number' => 2
          ],
          'colCount' => [
            'char' => 'C',
            'number' => 3
          ],
          'colPrice' => [
            'char' => 'D',
            'number' => 4
          ],
          'colTotal' => [
            'char' => 'E',
            'number' => 5
          ]
        ],
        'total' => [
          'colName' => [
            'char' => 'D',
            'number' => 4
          ],
          'colValue' => [
            'char' => 'E',
            'number' => 5
          ]
        ]
      ];
      //endregion

      //region Create Order Info EXCEL file
      // Create new Spreadsheet object
      $spreadsheet = new Spreadsheet();
      // Set document properties
      $spreadsheet->getProperties()
        ->setCreator($this->config->get('config_name'))
        ->setTitle('Order Info')
        ->setSubject('Order Info');

      $spreadsheet->setActiveSheetIndex(0);

      //region Column Width
      //  Name
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colName']['number'])
        ->setWidth(95);

      //  Model
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colModel']['number'])
        ->setWidth(20);

      //  Count
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colCount']['number'])
        ->setWidth(20);

      //  Price
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colPrice']['number'])
        ->setWidth(20);

      //  Total
      $spreadsheet
        ->getActiveSheet()
        ->getColumnDimensionByColumn($excelData['products']['colTotal']['number'])
        ->setWidth(20);
      //endregion

      $languageId = (int)$this->config->get('config_language_id');
      $fileName = "order-info-{$order->getOrderId()}.xls";
      $iRow = 1;

      //region Contact Info
      //  First name
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('Имя')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getFirstName())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Last name
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('Фамилия')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getLastName())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Email
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('Email')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getEmail())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;
      //endregion

      //region Order Info
      //  Order ID
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_order_id'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getOrderId())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->getStyle()
        ->getNumberFormat()
        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

      $iRow++;

      //  Date added
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_date_added'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue(date('d.m.Y', strtotime($order->getDateAdded())))
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Status
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('Статус')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value

      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getOrderStatusName())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Payment type
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_payment_method'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getPaymentMethod())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Delivery type
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_shipping_method'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getShippingMethod())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  TTN
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue('ТТН')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue((string)$order->getTtn() . ' ')
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->getStyle()
        ->getNumberFormat()
        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
      $iRow++;

      //  TTN Status
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_tracking'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value

      // Get TTN status
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getTtnStatus())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;

      //  Shipping address
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colName']['number'], $iRow)
        ->setValue($this->language->get('text_shipping_address'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['orderInfo']['colValue']['number'], $iRow)
        ->setValue($order->getShippingAddress1())
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);
      //endregion

      //region Products Header
      //  Name
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colName']['number'], $iRow)
        ->setValue($this->language->get('column_name'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Model
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colModel']['number'], $iRow)
        ->setValue($this->language->get('column_model'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Count
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colCount']['number'], $iRow)
        ->setValue($this->language->get('column_quantity'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Price
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colPrice']['number'], $iRow)
        ->setValue($this->language->get('column_price'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Total
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['products']['colTotal']['number'], $iRow)
        ->setValue($this->language->get('column_total'))
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);

      $iRow++;
      //endregion

      //region Products
      // Products
      $data['products'] = array();

      $products = $this->model_account_order->getOrderProducts($order->getOrderId());

      foreach ($products as $product) {
        //  Product description
        $productDescriptionRow = $productDescriptionModel->get((int)$product['product_id'], $languageId, true);

        //  Name
        $productName =  empty($productDescriptionRow) ? '' : $productDescriptionRow->getName();
        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colName']['number'], $iRow)
          ->setValue($productName)
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        //  Model
        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colModel']['number'], $iRow)
          ->setValue($product['model'])
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        //  Quantity
        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colCount']['number'], $iRow)
          ->setValue($product['quantity'])
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colPrice']['number'], $iRow)
          ->setValue($this->currency->format($product['price']))
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        $spreadsheet
          ->getActiveSheet()
          ->getCellByColumnAndRow($excelData['products']['colTotal']['number'], $iRow)
          ->setValue($this->currency->format($product['total']))
          ->getStyle()
          ->getFont()
          ->setSize($excelData['fontSize_1']);

        $iRow++;
      }
      //endregion

      //region Total
      $totals = $this->model_account_order->getOrderTotals($order->getOrderId());
      $total = $this->currency->format(Util::getArrItem($totals, '0.value', 0));

      //  Total
      //  Label
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['total']['colName']['number'], $iRow)
        ->setValue('Сумма')
        ->getStyle()
        ->getFont()
        ->setBold(true)
        ->setSize($excelData['fontSize_2']);
      //  Value
      $spreadsheet
        ->getActiveSheet()
        ->getCellByColumnAndRow($excelData['total']['colValue']['number'], $iRow)
        ->setValue($total)
        ->getStyle()
        ->getFont()
        ->setSize($excelData['fontSize_1']);

      $iRow++;
      //endregion
      //endregion

      //region Save
      // Set active sheet index to the first sheet, so Excel opens this as the first sheet
      $spreadsheet->setActiveSheetIndex(0);

      $writer = IOFactory::createWriter($spreadsheet, 'Xls');
      $writer->save(DIR_DOWNLOAD . '/' . $fileName);

      $data = [];
      $data['downloadUrl'] = '/system/storage/download/' . $fileName;
      $data['fileName'] = $fileName;
      //endregion

      $success = true;
      $msg = self::MSG_SUCCESS;
      $code = 200;
    } catch (\Exception $ex) {
      $msg = $ex->getMessage();
      $code = $ex->getMessage();
      $data = [];
    }

    return $this->_prepareJson([
      'success' => $success,
      'message' => $msg,
      'code' => $code,
      'data' => $data
    ]);
  }
}
