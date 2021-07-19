<?
class ControllerPriceList extends Controller {
  public function index() {
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

    $spreadsheet->getProperties()
      ->setCreator('UkrMobil')
      ->setTitle('Price List')
      ->setSubject('Price List');

    $spreadsheet->setActiveSheetIndex(0);
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->getColumnDimensionByColumn(1)->setWidth(20); // catrgory0
    $sheet->getColumnDimensionByColumn(2)->setWidth(20); // catrgory1
    $sheet->getColumnDimensionByColumn(3)->setWidth(20); // catrgory2
    $sheet->getColumnDimensionByColumn(4)->setWidth(8); // brand
    $sheet->getColumnDimensionByColumn(5)->setWidth(10); // hyperlink
    $sheet->getColumnDimensionByColumn(6)->setWidth(10); // Code
    $sheet->getColumnDimensionByColumn(7)->setWidth(95); // Name
    $sheet->getColumnDimensionByColumn(8)->setWidth(15); // Count Chernivtsi
    $sheet->getColumnDimensionByColumn(9)->setWidth(15); // Count Rivne
    $sheet->getColumnDimensionByColumn(10)->setWidth(8); // Price
    $sheet->getColumnDimensionByColumn(11)->setWidth(11); // Order

    $headerStyle = $sheet->getStyleByColumnAndRow(1, 1, 11, 1);
    $headerStyle->getFont()->setBold(true)->setSize(8);
    $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->getCellByColumnAndRow(1, 1)->setValue('КАТЕГОРІЯ');
    $sheet->getCellByColumnAndRow(2, 1)->setValue('РОЗДІЛ');
    $sheet->getCellByColumnAndRow(3, 1)->setValue('ПІДРОЗДІЛ');
    $sheet->getCellByColumnAndRow(4, 1)->setValue('БРЕНД');
    $sheet->getCellByColumnAndRow(5, 1)->setValue('ПОСИЛАННЯ');
    $sheet->getCellByColumnAndRow(6, 1)->setValue('КОД ТОВАРА');
    $sheet->getCellByColumnAndRow(7, 1)->setValue('НАЗВА');
    $sheet->getCellByColumnAndRow(8, 1)->setValue('ЗАЛИШОК Чернівці');
    $sheet->getCellByColumnAndRow(9, 1)->setValue('ЗАЛИШОК Рівне');
    $sheet->getCellByColumnAndRow(10, 1)->setValue('ЦІНА, USD');
    $sheet->getCellByColumnAndRow(11, 1)->setValue('ЗАМОВЛЕННЯ');

    $sql = "
      SELECT
          COALESCE(cd0.name, cd1.name, cd2.name) AS categoryName0,
          CASE
              WHEN cd0.name IS NULL AND cd1.name IS NOT NULL THEN cd2.name
            WHEN cd1.name IS NULL THEN ''
              ELSE cd1.name
          END AS categoryName1,
          IF(cd0.name IS NULL OR cd1.name IS NULL, '', cd2.name) AS categoryName2,
          b.name AS brand,
          p.product_id AS id,
          pd.name,
          COALESCE(
            (SELECT price
              FROM oc_product_special
              WHERE product_id = p.product_id
                AND customer_group_id = {$this->customer->getGroupId()}
                AND (date_start = '0000-00-00' OR date_start < NOW())
                AND (date_end = '0000-00-00' OR date_end > NOW())
            ),
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = {$this->customer->getGroupId()}),
            p.price
          ) AS price,
          p.quantity AS quantityStore1,
          p.quantity_store_2 AS quantityStore2
        FROM oc_product p
        LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
        LEFT JOIN oc_product_to_category ptc ON ptc.product_id = p.product_id
        LEFT JOIN oc_category c2 ON c2.category_id = ptc.category_id
        LEFT JOIN oc_category_description cd2 ON cd2.category_id = c2.category_id
        LEFT JOIN oc_category c1 ON c1.category_id = c2.parent_id
        LEFT JOIN oc_category_description cd1 ON cd1.category_id = c1.category_id
        LEFT JOIN oc_category c0 ON c0.category_id = c1.parent_id
        LEFT JOIN oc_category_description cd0 ON cd0.category_id = c0.category_id
        LEFT JOIN products_models pm ON pm.product_id = p.product_id
        LEFT JOIN models m ON m.id = pm.model_id
        LEFT JOIN brands b ON b.id = m.brand_id
        WHERE p.status
        GROUP BY p.product_id
        ORDER BY categoryName0, categoryName1, categoryName2, pd.name
    ";

    $iRow = 2;
    foreach ($this->db->query($sql)->rows as $product) {
      $cellStyle = $sheet->getStyleByColumnAndRow(1, $iRow, 11, $iRow);
      $cellStyle->getFont()->setSize(8);
      $cellStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      $cellStyle = $sheet->getStyleByColumnAndRow(1, $iRow, 4, $iRow);
      $cellStyle->getFont()->setBold(true);
      $cellStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

      $sheet->getCellByColumnAndRow(1, $iRow)->setValue($product['categoryName0']);
      $sheet->getCellByColumnAndRow(2, $iRow)->setValue($product['categoryName1']);
      $sheet->getCellByColumnAndRow(3, $iRow)->setValue($product['categoryName2']);
      $sheet->getCellByColumnAndRow(4, $iRow)->setValue($product['brand']);

      $productLink = $this->url->link('product', ['product_id' => $product['id']]);
      $codeValue = "=HYPERLINK(\"{$productLink}\", \"Перейти\")";
      $sheet->getCellByColumnAndRow(5, $iRow)->setValue($codeValue);

      $sheet->getCellByColumnAndRow(6, $iRow)->setValue($product['id']);

      $cellName = $sheet->getCellByColumnAndRow(7, $iRow);
      $cellName->setValue($product['name']);
      $cellName->getStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

      $cellStore1 = $sheet->getCellByColumnAndRow(8, $iRow);
      $cellStore1->setValue($product['quantityStore1']);
      $cellStore1->getStyle()->getFont()->setBold(true);

      $cellStore2 = $sheet->getCellByColumnAndRow(9, $iRow);
      $cellStore2->setValue($product['quantityStore2']);
      $cellStore2->getStyle()->getFont()->setBold(true);

      $sheet->getCellByColumnAndRow(10, $iRow)->setValue($product['price']);
      ++$iRow;
    }


    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'price-list_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    $writer->save('php://output');
    exit();
  }
}
