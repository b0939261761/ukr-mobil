<?php

use Ego\Models\Stocks;
use Ego\Providers\Util;
use Ego\Providers\Validator;

class ControllerExtensionModuleEgoProductNew extends \Ego\Controllers\BaseController {

	public function index($setting) {
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$egoProductModel = new \Ego\Models\Product();
		$egoProductDescriptionModel = new \Ego\Models\ProductDescription();
		$egoProductToStockModel = new \Ego\Models\ProductToStock();
		$stocksModel = new Stocks();

		$data['productList'] = [];
		$productList = $egoProductModel->getNewList(9, true);
		$productList = empty($productList) ? [] : $productList;

		foreach ($productList as $product) {
			$stockList = [
				[
					'name' => 'г. Черновцы',
					'quantity' => $product->getQuantity()
				]
			];

			$productToStockList = $egoProductToStockModel->getListByProduct($product->getProductId(), true);
			$productToStockList = empty($productToStockList) ? [] : $productToStockList;

			foreach ($productToStockList as $productToStock) {
				$stockRow = $stocksModel->get($productToStock->getStockId(), true);

				if (empty($stockRow)) continue;

				$stockList[] = [
					'name' => $stockRow->getName(),
					'quantity' => $productToStock->getQuantity()
				];
			}

			$description = $egoProductDescriptionModel->get($product->getProductId(), 2, true);

			$product_info = $this->model_catalog_product->getProduct($product->getProductId());
			$price = Util::strToFloat($product_info['price']);
			$priceSpecial = Util::strToFloat($product_info['special']);

			//  Product image
			if (empty($product->getImage())) {
				$productImage = $image = $this->model_tool_image->resize(
					'placeholder.png',
					$this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
					$this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
				);
			} else {
				$productImage = '/image/' . $product->getImage();
			}

			$data['productList'][] = [
				'link' => $this->url->link('product/product', [ 'product_id' => $product->getProductId()]),
				'image' => $productImage,
				'name' => empty($description) ? '' : $description->getName(),
				'price' => $this->currency->format($price, $this->session->data['currency']),
				'special' => $this->currency->format($priceSpecial, $this->session->data['currency']),
				'sku' => $product->getSku(),
				'productCount' => $this->getProductCount($product->getProductId()),
				'stockList' => $stockList
			];

		}

		return $this->load->view('extension/module/ego_product_new', $data);
	}

	/**
	 * Return product count
	 *
	 * @param $productId - Product ID
	 * @return int
	 */
	private function getProductCount($productId) {
		$productId = (int)$productId;

		//region Define Models
		$egoProductToStockModel = new \Ego\Models\ProductToStock();
		//endregion

		return $egoProductToStockModel->getCount($productId);
	}

}
