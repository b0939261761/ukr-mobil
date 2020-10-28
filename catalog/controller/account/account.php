<?php

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
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/account');
		$this->document->setTitle($this->language->get('heading_title'));

		$data['logout'] = $this->url->link('account/logout', '', true);

		//region Define Models
		$customerModel = new Customer();
		$orderModel = new Order();
		//endregion

		//region Prepare data
		$data['mytemplate'] = $this->config->get('theme_default_directory');

		//region Current user info
		$customer = $customerModel->get($this->customer->getId(), true);

		$data['userInfo'] = [
			'firstName' => $customer->getFirstName(),
			'lastName' => $customer->getLastName(),
			'email' => $customer->getEmail(),
			'telephone' => $customer->getTelephone(),
			'region' => $customer->getRegion(),
			'city' => $customer->getCity(),
			'warehouse' => $customer->getWarehouse()
		];
		//endregion

		//region Purchases history
		$data['orderList'] = [];
		$orderList = $orderModel->getByCustomerId($this->customer->getId(), true);
		$orderList = empty($orderList) ? [] : $orderList;

		foreach ($orderList as $item) {
			$data['orderList'][] = [
				'order_id' => $item->getOrderId(),
				'date_added' => date('d.m.y', strtotime($item->getDateAdded())),
        'orderStatusName' => $item->getOrderStatusName(),
				'payment_type' => $item->getPaymentMethod(),
				'delivery_type' => $item->getShippingMethod(),
				'shippingFullname' => "{$item->getShippingFirstName()} {$item->getShippingLastName()}",
				'ttn' => $item->getTtn(),
        'storeName' => $item->getStoreName(),
				'ttnStatus' => $item->getTtnStatus(),
				'total' => $this->currency->format($item->getTotal(), $this->session->data['currency']),
				'url' => $this->url->link('account/order/info?', 'order_id=' . $item->getOrderId(), true)
			];
		}

    $data['balance'] = $this->getBalanceFrom1c();

		// ---------------------------------------------
		$postModel = new \Ego\Models\EgoPost();
		$postContentModel = new \Ego\Models\EgoPostContent();
		$currentLangId = $this->config->get('config_language_id');

		$data['terms_of_sale'] = '';
		$post = $postModel->getByCategory('terms_of_sale', true);

		if (!empty($post)) {
			$postContent = $postContentModel->getByPost($post[0]->getId(), $currentLangId, false, true);
			$data['terms_of_sale'] = empty($postContent) ? '' : $postContent[0]->getContent();
		}

    $sql = "SELECT type_price, credit_limit, payment_delay, manager_fullname
      FROM oc_customer WHERE customer_id = {$this->customer->getId()}";

		$query = $this->db->query($sql);

		$row = $query->rows[0];
		$data['type_price'] = $row['type_price'];
		$data['credit_limit'] = $row['credit_limit'];
		$data['payment_delay'] = $row['payment_delay'];
    $data['manager_fullname'] = $row['manager_fullname'];

		// ---------------------------------------------

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/account', $data));
	}

	public function saveProfile() {
		$success = false;
		$msg = self::MSG_INTERNAL_ERROR;
		$data = [];
		$baseModel = new BaseModel();

		try {
			$this->onlyPost();
			$baseModel->_getDb()->beginTransaction();

			//region Input Data
			$transferData = $this->getInput('transferData');
			//endregion

			//region Check required fields is not empty
			if (($errorField = Validator::isRequiredFieldsEmpty($transferData))) {
				$description = Util::getArrItem($errorField, 'description', '');

				throw new \RuntimeException("Field '{$description}' must be filled.");
			}
			//endregion

			//region Define Models
			$customerModel = new Customer();
			//endregion

			//region Update Customer info
			$customerRow = $customerModel->get($this->customer->getId(), true);
			$customerRow->setFirstName(Util::getArrItem($transferData, 'name.value', ''))
				->setLastName(Util::getArrItem($transferData, 'surname.value', ''))
				->setEmail(Util::getArrItem($transferData, 'email.value', ''))
				->setTelephone(Util::getArrItem($transferData, 'phone_number.value', ''))
				->setRegion(Util::getArrItem($transferData, 'region.value', ''))
				->setCity(Util::getArrItem($transferData, 'city.value', ''))
				->setWarehouse(Util::getArrItem($transferData, 'warehouse.value', ''));

			if (!$customerModel->update($customerRow)) {
				throw new \Exception("Error occurred while update customer.");
			}
			//endregion

			//region Update Customer password
			$password = Util::getArrItem($transferData, 'password.value', '');

			if (!empty($password)) {
				$salt = $this->db->escape($salt = token(9));
				$password = $this->db->escape(sha1($salt . sha1($salt . sha1($password))));

				if (!$customerModel->updatePassword($customerRow->getCustomerId(), $password, $salt)) {
					throw new \Exception("Error occurred while update customer.");
				}
			}
			//endregion

			$success = true;
			$msg = self::MSG_SUCCESS;

			$baseModel->_getDb()->commit();
		} catch (\Exception $ex) {
			$baseModel->_getDb()->rollBack();

			$msg = $ex->getMessage();
		}

		$this->_prepareJson([
			'success' => $success,
			'msg' => $msg,
			'data' => $data
		]);
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id' => $country_info['country_id'],
				'name' => $country_info['name'],
				'iso_code_2' => $country_info['iso_code_2'],
				'iso_code_3' => $country_info['iso_code_3'],
				'address_format' => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone' => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status' => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
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
			//	Name
			$spreadsheet
				->getActiveSheet()
				->getColumnDimensionByColumn($excelData['products']['colName']['number'])
				->setWidth(95);

			//	Model
			$spreadsheet
				->getActiveSheet()
				->getColumnDimensionByColumn($excelData['products']['colModel']['number'])
				->setWidth(20);

			//	Count
			$spreadsheet
				->getActiveSheet()
				->getColumnDimensionByColumn($excelData['products']['colCount']['number'])
				->setWidth(20);

			//	Price
			$spreadsheet
				->getActiveSheet()
				->getColumnDimensionByColumn($excelData['products']['colPrice']['number'])
				->setWidth(20);

			//	Total
			$spreadsheet
				->getActiveSheet()
				->getColumnDimensionByColumn($excelData['products']['colTotal']['number'])
				->setWidth(20);
			//endregion

			$languageId = (int)$this->config->get('config_language_id');
			$fileName = $this->getDownloadExcelOrderInfoFileName($order->getOrderId());
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
				->setValue(date($this->language->get('date_format_short'), strtotime($order->getDateAdded())))
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
				//	Product description
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

				//  Price
				$price = $this->currency->format(
					$product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0),
					$this->session->data['currency']
				);
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($excelData['products']['colPrice']['number'], $iRow)
					->setValue($price)
					->getStyle()
					->getFont()
					->setSize($excelData['fontSize_1']);

				//  Total
				$price = $this->currency->format(
					$product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0),
					$this->session->data['currency']
				);
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($excelData['products']['colTotal']['number'], $iRow)
					->setValue($price)
					->getStyle()
					->getFont()
					->setSize($excelData['fontSize_1']);

				$iRow++;
			}
			//endregion

			//region Total
			$totals = $this->model_account_order->getOrderTotals($order->getOrderId());
			$total = $this->currency->format(
				Util::getArrItem($totals, '0.value', 0),
				$this->session->data['currency']
			);

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

	/**
	 * Return balance from 1C
	 *
	 * @return mixed|string
	 */
	public function balanceFrom1c() {
		$success = false;
		$msg = self::MSG_INTERNAL_ERROR;
		$code = 500;
		$data = [];

		try {
			//region Input Data
			$transferData = $this->getInput('transferData');
			//endregion

			$dateFrom = Util::getArrItem($transferData, 'dateFrom');
			$dateTo = Util::getArrItem($transferData, 'dateTo');

			//  Date From
			$_dateFrom = DateTime::createFromFormat('d.m.Y', $dateFrom);

			if ($_dateFrom instanceof DateTime) {
				$dateFrom = $_dateFrom->format('Ymd');
			} else {
				$dateFrom = '20000101';
			}

			//  Date From
			$_dateTo = DateTime::createFromFormat('d.m.Y', $dateTo);

			if ($_dateTo instanceof DateTime) {
				$dateTo = $_dateTo->format('Ymd');
			} else {
				$dateTo = date('Ymd');
			}

			$data = $this->getBalanceFrom1c($dateFrom, $dateTo);

			$success = true;
			$msg = self::MSG_SUCCESS;
			$code = 200;
		} catch (\Exception $ex) {
			$msg = $ex->getMessage();
			$code = $ex->getCode();
			$data = [];
		}

		return $this->_prepareJson([
			'success' => $success,
			'msg' => $msg,
			'code' => $code,
			'data' => $data
		]);
	}

	/**
	 * Return download excel file name for order info
	 *
	 * @param $orderId
	 * @return string
	 */
	private function getDownloadExcelOrderInfoFileName($orderId) {
		return "order-info-{$orderId}.xls";
	}

	/**
	 * Return data from 1C
	 *
	 * @param null $dateFrom
	 * @param null $dateTo
	 * @return array|mixed
	 */
	private function getBalanceFrom1c($dateFrom = null, $dateTo = null) {
		//  Date From
		if (empty($dateFrom)) {
			$dateFrom = '20000101';
		}

		//  Date To
		if (empty($dateTo)) {
			$dateTo = date('Ymd');
		}

		$customerId = $this->customer->getId();
		$baseUri = 'http://API:1@um.reality.sh';
		$uri = "/pavel_ut/hs/get_data/dtkt/id={$customerId}&startdate={$dateFrom}&enddate={$dateTo}";

		try {
			$client = new GuzzleHttp\Client([ 'base_uri' => $baseUri ]);
			$response = $client->request('GET', $uri, [ 'timeout' => 3.14 ]);
			$result = json_decode($response->getBody()->getContents(), true);

			foreach ($result as $key => $value) {
				$value['order_url'] = $this->url->link('account/order/info', 'order_id=' . $value['Order'], true);
				$result[$key] = $value;
			}
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			echo $e->getMessage() . "URI: <code>{$baseUri}{$uri}</code> <br>";
			return [];
		}

		return $result;
	}

}
