<?php

use Ego\Models\Category;
use Ego\Models\CategoryDescription;
use Ego\Models\Product;
use Ego\Models\ProductDescription;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ControllerAjaxEgo extends \Ego\Controllers\BaseController {

	/**
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	public function downloadLinkExcelPriceList() {
		$this->excelPriceList('download');
	}

	/**
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	public function downloadExcelPriceList() {
		$success = false;
		$msg = self::MSG_INTERNAL_ERROR;
		$data = [];

		try {
			$priceList = new \Ego\Services\PriceList();
			$fileName = $priceList->excelPriceList();

			$data = [];
			$data['downloadUrl'] = '/system/storage/download/' . $fileName;
			$data['fileName'] = $fileName;

			$success = true;
			$msg = self::MSG_SUCCESS;
		} catch (\Exception $ex) {
			$msg = $ex->getMessage();
		}

		$this->_prepareJson([
			'success' => $success,
			'msg' => $msg,
			'code' => 200,
			'data' => $data
		]);
	}

	/**
	 * Generate price list
	 *
	 * @param string $save
	 * @return string
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function excelPriceList($save = 'file') {
		//region Prepare Data
		$customerGroupId = 1;

		if ($this->customer->isLogged()) {
			$customerGroupId = $this->customer->getGroupId();
		}

		$data = [
			'color_1' => 'eeeeee',
			'fontSize_1' => 8,
			'fontSize_2' => 11,
			'colCode' => [
				'char' => 'A',
				'number' => 1
			],
			'colName' => [
				'char' => 'B',
				'number' => 2
			],
			'colCountChernivtsi' => [
				'char' => 'C',
				'number' => 3
			],
			'colCountRivne' => [
				'char' => 'D',
				'number' => 4
			],
			'colPrice' => [
				'char' => 'E',
				'number' => 5
			],
			'colOrder' => [
				'char' => 'F',
				'number' => 6
			]
		];
		$languageId = (int)$this->config->get('config_language_id');
		$fileName = $this->getDownloadExcelPriceListFileName();
		$iRow = 1;
		//endregion

		//region Create Spreadsheet
		// Create new Spreadsheet object
		$spreadsheet = new Spreadsheet();
		// Set document properties
		$spreadsheet->getProperties()
			->setCreator($this->config->get('config_name'))
			->setTitle('Price List')
			->setSubject('Price List');

		$spreadsheet->setActiveSheetIndex(0);

		//region Column Width
		//	Code
		$spreadsheet
			->getActiveSheet()
			->getColumnDimensionByColumn($data['colCode']['number'])
			->setWidth(12);

		//	Name
		$spreadsheet
			->getActiveSheet()
			->getColumnDimensionByColumn($data['colName']['number'])
			->setWidth(95);

		//	Count Chernivtsi
		$spreadsheet
			->getActiveSheet()
			->getColumnDimensionByColumn($data['colCountChernivtsi']['number'])
			->setWidth(8);

		//	Count Rivne
		$spreadsheet
			->getActiveSheet()
			->getColumnDimensionByColumn($data['colCountRivne']['number'])
			->setWidth(8);

		//	Price
		$spreadsheet
			->getActiveSheet()
			->getColumnDimensionByColumn($data['colPrice']['number'])
			->setWidth(8);

		//	Order
		$spreadsheet
			->getActiveSheet()
			->getColumnDimensionByColumn($data['colPrice']['number'])
			->setWidth(8);
		//endregion
		//endregion

		//region Define Models
		$categoryModel = new Category();
		$categoryDescriptionModel = new CategoryDescription();
		$productModel = new Product();
		$productDescriptionModel = new ProductDescription();
		$productDiscountModel = new \Ego\Models\ProductDiscount();
		//endregion

		$categoryDescriptionList = $categoryDescriptionModel->getList($languageId, true);
		$categoryDescriptionList = empty($categoryDescriptionList) ? [] : $categoryDescriptionList;

		foreach ($categoryDescriptionList as $categoryDescription) {
			$category = $categoryModel->get($categoryDescription->getCategoryId(), true);

			if (empty($category) || $category->getStatus() !== 1) {
				continue;
			}

			//region Category Title
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colCode']['number'], $iRow)
				->setValue($categoryDescription->getName())
				->getStyle()
				->getFont()
				->setBold(true)
				->setSize($data['fontSize_2']);

			$spreadsheet
				->getActiveSheet()
				->mergeCellsByColumnAndRow($data['colCode']['number'], $iRow, $data['colOrder']['number'], $iRow)
				->getStyleByColumnAndRow($data['colCode']['number'], $iRow, $data['colOrder']['number'], $iRow)
				->getFill()
				->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()
				->setRGB($data['color_1']);

			$iRow++;
			//endregion

			//region Product header
			//	Product header Code
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colCode']['number'], $iRow)
				->setValue('КОД ТОВАРА')
				->getStyle()
				->getFont()
				->setBold(true)
				->setSize($data['fontSize_1']);
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colCode']['number'], $iRow)
				->getStyle()
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

			//	Product header Name
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colName']['number'], $iRow)
				->setValue('НАЗВАНИЕ')
				->getStyle()
				->getFont()
				->setBold(true)
				->setSize($data['fontSize_1']);

			//	Product header Count Chernivtsi
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colCountChernivtsi']['number'], $iRow)
				->setValue('ОСТАТКИ Черновцы')
				->getStyle()
				->getFont()
				->setBold(true)
				->setSize($data['fontSize_1']);
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colCountChernivtsi']['number'], $iRow)
				->getStyle()
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

			//	Product header Count Rivne
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colCountRivne']['number'], $iRow)
				->setValue('ОСТАТКИ Ровно')
				->getStyle()
				->getFont()
				->setBold(true)
				->setSize($data['fontSize_1']);
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colCountRivne']['number'], $iRow)
				->getStyle()
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

			//	Product header Price
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colPrice']['number'], $iRow)
				->setValue('ЦЕНА(' . strtoupper($this->session->data['currency']) . ')')
				->getStyle()
				->getFont()
				->setBold(true)
				->setSize($data['fontSize_1']);
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colPrice']['number'], $iRow)
				->getStyle()
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

			//	Product header Order
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colOrder']['number'], $iRow)
				->setValue('ЗАКАЗ')
				->getStyle()
				->getFont()
				->setBold(true)
				->setSize($data['fontSize_1']);
			$spreadsheet
				->getActiveSheet()
				->getCellByColumnAndRow($data['colOrder']['number'], $iRow)
				->getStyle()
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

			$iRow++;
			//endregion

			//region Products
			$productList = $productModel->getByCategoryIdForPrice($categoryDescription->getCategoryId());
			$productList = empty($productList) ? [] : $productList;

			foreach ($productList as $product) {
				if ($product->getStatus() !== 1) {
					continue;
				}

				$productDescription = $productDescriptionModel->get($product->getProductId(), $languageId, true);
				$productDiscount = $productDiscountModel->getProductAndGroup($product->getProductId(), $customerGroupId, true);
				$price = empty($productDiscount) ? $product->getPrice() : $productDiscount->getPrice();

				//	Product Code
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colCode']['number'], $iRow)
					->setValue($product->getProductId())
					->getStyle()
					->getFont()
					->setSize($data['fontSize_1']);
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colCode']['number'], $iRow)
					->getStyle()
					->getAlignment()
					->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

				//	Product Name
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colName']['number'], $iRow)
					->setValue($productDescription->getName())
					->getStyle()
					->getFont()
					->setSize($data['fontSize_1']);

				//	Product Count Chernivtsi
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colCountChernivtsi']['number'], $iRow)
					->setValue($product->getQuantity())
					->getStyle()
					->getFont()
					->setBold(true)
					->setSize($data['fontSize_1']);
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colCountChernivtsi']['number'], $iRow)
					->getStyle()
					->getAlignment()
					->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

				//	Product Count Rivne
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colCountRivne']['number'], $iRow)
					->setValue($product->getQuantity2())
					->getStyle()
					->getFont()
					->setBold(true)
					->setSize($data['fontSize_1']);
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colCountRivne']['number'], $iRow)
					->getStyle()
					->getAlignment()
					->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

				//	Product Price
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colPrice']['number'], $iRow)
					->setValue($price)
					->getStyle()
					->getFont()
					->setSize($data['fontSize_1']);
				$spreadsheet
					->getActiveSheet()
					->getCellByColumnAndRow($data['colPrice']['number'], $iRow)
					->getStyle()
					->getAlignment()
					->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

				$iRow++;
			}
			//endregion
		}

		//region Save
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$spreadsheet->setActiveSheetIndex(0);

		$writer = IOFactory::createWriter($spreadsheet, 'Xls');

		switch ($save) {
			case 'file':
				$writer->save(DIR_DOWNLOAD . '/' . $fileName);

				break;

			case 'download':
				header('Content-type: application/vnd.ms-excel');
				header('Content-Disposition: attachment; filename="price-list.xls"');
				$writer->save('php://output');

				die();
		}
		//endregion

		return $fileName;
	}

	/**
	 * Return download excel price list file name
	 *
	 * @return string
	 */
	private function getDownloadExcelPriceListFileName() {
		$customerGroupId = 1;

		if ($this->customer->isLogged()) {
			$customerGroupId = $this->customer->getGroupId();
		}

		return "price-list-{$customerGroupId}.xls";
	}

}
