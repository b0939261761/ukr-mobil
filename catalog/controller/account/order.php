<?
class ControllerAccountOrder extends Controller {
  public function index() {
    $data['orderId'] = (int)($this->request->get['order_id'] ?? 0);

    // if (!$this->customer->isLogged()) {
    //   $this->session->data['redirect'] = $this->url->link('account/order', ['order_id' => $data['orderId']]);
    //   $this->response->redirect($this->url->link('account/login'));
    // }

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

  public function download() {
    $orderId = (int)($this->request->get['id'] ?? 0);
    $orderNew = $this->getOrder($orderId);

    $formatText = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT;
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadsheet->getProperties()
      ->setCreator('UkrMobil')
      ->setTitle('Order Info')
      ->setSubject('Order Info');

    $spreadsheet->setActiveSheetIndex(0);
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->getColumnDimensionByColumn(1)->setWidth(95);
    $sheet->getColumnDimensionByColumn(2)->setWidth(20);
    $sheet->getColumnDimensionByColumn(3)->setWidth(20);
    $sheet->getColumnDimensionByColumn(4)->setWidth(20);

    $sheet->getStyleByColumnAndRow(1, 1, 1, 11)->getFont()->setBold(true)->setSize(11);
    $sheet->getStyleByColumnAndRow(2, 1, 2, 11)->getFont()->setSize(8);

    $sheet->getCellByColumnAndRow(1, 1)->setValue('Контрагент');
    $sheet->getCellByColumnAndRow(2, 1)->setValue($orderNew['shippingFullName']);

    $sheet->getCellByColumnAndRow(1, 2)->setValue('Email');
    $sheet->getCellByColumnAndRow(2, 2)->setValue($orderNew['email']);

    $sheet->getCellByColumnAndRow(1, 3)->setValue('Номер заказа');
    $cell = $sheet->getCellByColumnAndRow(2, 3);
    $cell->getStyle()->getNumberFormat()->setFormatCode($formatText);
    $cell->setValue($orderId);

    $sheet->getCellByColumnAndRow(1, 4)->setValue('Добавлено');
    $sheet->getCellByColumnAndRow(2, 4)->setValue($orderNew['dateAdded']);

    $sheet->getCellByColumnAndRow(1, 5)->setValue('Статус');
    $sheet->getCellByColumnAndRow(2, 5)->setValue($orderNew['statusName']);

    $sheet->getCellByColumnAndRow(1, 6)->setValue('Способ оплаты');
    $sheet->getCellByColumnAndRow(2, 6)->setValue($orderNew['paymentMethod']);

    $sheet->getCellByColumnAndRow(1, 7)->setValue('Тип доставки');
    $sheet->getCellByColumnAndRow(2, 7)->setValue($orderNew['shippingMethod']);

    $sheet->getCellByColumnAndRow(1, 8)->setValue('ТТН');
    $cell = $sheet->getCellByColumnAndRow(2, 8);
    $cell->getStyle()->getNumberFormat()->setFormatCode($formatText);
    $cell->setValue($orderNew['ttn']);

    $sheet->getCellByColumnAndRow(1, 9)->setValue('Отслеживание');
    $sheet->getCellByColumnAndRow(2, 9)->setValue($orderNew['ttnStatus']);

    $sheet->getCellByColumnAndRow(1, 10)->setValue('Адрес доставки');
    $sheet->getCellByColumnAndRow(2, 10)->setValue($orderNew['shippingAddress']);

    $sheet->getStyleByColumnAndRow(1, 12, 4, 12)->getFont()->setBold(true)->setSize(11);
    $sheet->getCellByColumnAndRow(1, 12)->setValue('Название товара');
    $sheet->getCellByColumnAndRow(2, 12)->setValue('Количество');
    $sheet->getCellByColumnAndRow(3, 12)->setValue('Цена');
    $sheet->getCellByColumnAndRow(4, 12)->setValue('Всего');

    $iRow = 13;
    foreach ($this->getOrderProducts($orderId) as $product) {
      $sheet->getStyleByColumnAndRow(1, $iRow, 4, $iRow)->getFont()->setSize(8);
      $sheet->getCellByColumnAndRow(1, $iRow)->setValue($product['name']);
      $sheet->getCellByColumnAndRow(2, $iRow)->setValue($product['quantity']);
      $sheet->getCellByColumnAndRow(3, $iRow)->setValue("{$product['priceUAH']} грн. ({$product['priceUSD']})");
      $sheet->getCellByColumnAndRow(4, $iRow)->setValue("{$product['totalUAH']} грн. ({$product['totalUSD']})");
      $iRow++;
    }

    $cell = $sheet->getCellByColumnAndRow(3, $iRow);
    $cell->getStyle()->getFont()->setBold(true)->setSize(11);
    $cell->setValue('Сумма');

    $cell = $sheet->getCellByColumnAndRow(4, $iRow);
    $cell->getStyle()->getFont()->setSize(8);
    $cell->setValue("{$orderNew['totalUAH']} грн. ({$orderNew['totalUSD']})");

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = "order-{$orderId}.xlsx";
    header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    $writer->save('php://output');
    exit();
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
