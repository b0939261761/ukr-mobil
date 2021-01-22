<?
class ControllerInformationPriceList extends Controller {
  public function index() {
    $iRow = 1;
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

    $spreadsheet->getProperties()
      ->setCreator('UkrMobil')
      ->setTitle('Price List')
      ->setSubject('Price List');

    $spreadsheet->setActiveSheetIndex(0);
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->getColumnDimensionByColumn(1)->setWidth(10); // hyperlink
    $sheet->getColumnDimensionByColumn(2)->setWidth(10); // Code
    $sheet->getColumnDimensionByColumn(3)->setWidth(95); // Name
    $sheet->getColumnDimensionByColumn(4)->setWidth(15); // Count Chernivtsi
    $sheet->getColumnDimensionByColumn(5)->setWidth(15); // Count Rivne
    $sheet->getColumnDimensionByColumn(6)->setWidth(8); // Price
    $sheet->getColumnDimensionByColumn(7)->setWidth(8); // Order

    $this->loopCategory($sheet, $this->getCategories(), 1);

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'price-list_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    $writer->save('php://output');
    exit();
  }

  private function getCategories() {
    $customerGroupId = (int)($this->customer->getGroupId() ?? 1);

    $sql = "
      WITH
        tmpProducts AS (
          SELECT
            ptc.category_id,
            p.product_id AS id,
            pd.name,
            COALESCE(
              (SELECT price
                FROM oc_product_special
                WHERE product_id = p.product_id
                  AND customer_group_id = {$customerGroupId}
                  AND (date_start = '0000-00-00' OR date_start < NOW())
                  AND (date_end = '0000-00-00' OR date_end > NOW())
                ORDER BY priority ASC, price ASC LIMIT 1),
              pdc.price,
              p.price) AS price,
              p.quantity AS quantityStore1,
              p.quantity_store_2 AS quantityStore2
          FROM oc_product p
          LEFT JOIN oc_product_to_category ptc ON ptc.product_id = p.product_id
          LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
          LEFT JOIN oc_product_discount pdc ON pdc.product_id = p.product_id
            AND pdc.customer_group_id = {$customerGroupId}
          ORDER BY pd.name
        ),
        tmpGroupProducts AS (
          SELECT
            category_id,
            JSON_ARRAYAGG(JSON_OBJECT(
              'id', id, 'name', name, 'price', price,
              'quantityStore1', quantityStore1, 'quantityStore2', quantityStore2
            )) AS products
          FROM tmpProducts
          GROUP BY category_id
        )
      SELECT
        IF(count(name), JSON_ARRAYAGG(JSON_OBJECT('name', name, 'children', children, 'products', products)), JSON_ARRAY()) AS value
      FROM (
        SELECT
          cd1.name,
          IF(count(t2.name), JSON_ARRAYAGG(JSON_OBJECT('name', t2.name, 'children', t2.children, 'products', t2.products)), JSON_ARRAY()) AS children,
          COALESCE(tgp1.products, JSON_ARRAY()) AS products
          FROM oc_category c1
        LEFT JOIN oc_category_description cd1 ON cd1.category_id = c1.category_id
        LEFT JOIN tmpGroupProducts tgp1 ON tgp1.category_id = c1.category_id
        LEFT JOIN LATERAL (
          SELECT
            cd2.name,
            IF(count(t3.name), JSON_ARRAYAGG(JSON_OBJECT('name', t3.name, 'children', t3.children, 'products', t3.products)), JSON_ARRAY()) AS children,
            COALESCE(tgp2.products, JSON_ARRAY()) AS products
          FROM oc_category c2
          LEFT JOIN oc_category_description cd2 ON cd2.category_id = c2.category_id
          LEFT JOIN tmpGroupProducts tgp2 ON tgp2.category_id = c2.category_id
          LEFT JOIN LATERAL (
            SELECT
              cd3.name,
              JSON_ARRAY() AS children,
              COALESCE(tgp3.products, JSON_ARRAY()) AS products
            FROM oc_category c3
            LEFT JOIN oc_category_description cd3 ON cd3.category_id = c3.category_id
            LEFT JOIN tmpGroupProducts tgp3 ON tgp3.category_id = c3.category_id
            WHERE c3.parent_id = c2.category_id AND c3.status = 1
            ORDER BY c3.sort_order, cd3.name
          ) AS t3 ON true
          WHERE c2.parent_id = c1.category_id AND c2.status = 1
          GROUP BY c2.category_id
          ORDER BY c2.sort_order, cd2.name
        ) AS t2 ON true
        WHERE c1.parent_id = 0 AND c1.status = 1
        GROUP BY c1.category_id
        ORDER BY c1.sort_order, cd1.name
      ) AS t1
    ";

    return json_decode($this->db->query($sql)->row['value'], true);
  }

  private function loopCategory ($sheet, $categories, $iRow) {
    $horizontalCenter = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
    $horizontalLeft = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
    $fillSolid = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;

    foreach ($categories as $category) {
      $sheet->mergeCellsByColumnAndRow(1, $iRow, 7, $iRow);
      $cellCategory = $sheet->getCellByColumnAndRow(1, $iRow);
      $cellCategory->setValue($category['name']);
      $cellCategoryStyle = $cellCategory->getStyle();
      $cellCategoryStyle->getFont()->setBold(true)->setSize(11);
      $cellCategoryStyle->getFill()->setFillType($fillSolid)->getStartColor()->setRGB('eeeeee');

      ++$iRow;

      $products = $category['products'];
      if (count($products)) {
        $sheet->getCellByColumnAndRow(1, $iRow)->setValue('CСЫЛКА');
        $sheet->getCellByColumnAndRow(2, $iRow)->setValue('КОД ТОВАРА');
        $sheet->getCellByColumnAndRow(3, $iRow)->setValue('НАЗВАНИЕ');
        $sheet->getCellByColumnAndRow(4, $iRow)->setValue('ОСТАТКИ Черновцы');
        $sheet->getCellByColumnAndRow(5, $iRow)->setValue('ОСТАТКИ Ровно');
        $sheet->getCellByColumnAndRow(6, $iRow)->setValue('Цена, USD');
        $sheet->getCellByColumnAndRow(7, $iRow)->setValue('ЗАКАЗ');
        $headerStyle = $sheet->getStyleByColumnAndRow(1, $iRow, 7, $iRow);
        $headerStyle->getFont()->setBold(true)->setSize(8);
        $headerStyle->getAlignment()->setHorizontal($horizontalCenter);
        ++$iRow;

        foreach ($products as $product) {
          $cellStyle = $sheet->getStyleByColumnAndRow(1, $iRow, 7, $iRow);
          $cellStyle->getFont()->setSize(8);
          $cellStyle->getAlignment()->setHorizontal($horizontalCenter);

          $productLink = $this->url->link('product/product', ['product_id' => $product['id']]);
          $codeValue = "=HYPERLINK(\"{$productLink}\", \"Перейти\")";
          $sheet->getCellByColumnAndRow(1, $iRow)->setValue($codeValue);

          $sheet->getCellByColumnAndRow(2, $iRow)->setValue($product['id']);

          $cellName = $sheet->getCellByColumnAndRow(3, $iRow);
          $cellName->setValue($product['name']);
          $cellName->getStyle()->getAlignment()->setHorizontal($horizontalLeft);

          $cellStore1 = $sheet->getCellByColumnAndRow(4, $iRow);
          $cellStore1->setValue($product['quantityStore1']);
          $cellStore1->getStyle()->getFont()->setBold(true);

          $cellStore2 = $sheet->getCellByColumnAndRow(5, $iRow);
          $cellStore2->setValue($product['quantityStore2']);
          $cellStore2->getStyle()->getFont()->setBold(true);

          $sheet->getCellByColumnAndRow(6, $iRow)->setValue($product['price']);
          ++$iRow;
        }
      }

      $children = $category['children'];
      if (count($children)) {
        $iRow = $this->loopCategory($sheet, $children, $iRow);
      }
    }

    return $iRow;
  }
}
