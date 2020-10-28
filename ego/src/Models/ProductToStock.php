<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\ProductToStockRowStruct;

class ProductToStock extends BaseModel {

	/**
	 * Add row
	 *
	 * @param ProductToStockRowStruct $row
	 * @return string
	 */
	public function add(ProductToStockRowStruct $row) {
		$sql = "
			INSERT INTO oc_product_to_stock
			SET
				product_id = :product_id,
				stock_id   = :stock_id,
				quantity   = :quantity
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $row->getProductId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':stock_id', $row->getStockId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':quantity', $row->getQuantity(), \PDO::PARAM_INT);
		$dataQuery->execute();

		return $this->_getDb()->lastInsertId();
	}

	/**
	 * Delete row
	 *
	 * @param int $productId
	 * @param int $stockId
	 * @return bool
	 */
	public function delete(int $productId, int $stockId) {
		$sql = "
			DELETE FROM oc_product_to_stock
			WHERE 1
				  AND product_id = :product_id
				  AND stock_id = :stock_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':stock_id', $stockId, \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Return list by product ID
	 *
	 * @param int $productId
	 * @param bool|null $isStruct
	 * @return array|ProductToStockRowStruct[]|null
	 */
	public function getListByProduct(int $productId, bool $isStruct = null) {
		$sql = "
			SELECT
			
				opts.product_id,
				opts.stock_id,
				opts.quantity
			
			FROM oc_product_to_stock opts
			
			WHERE 1
				  AND opts.product_id = :product_id

			ORDER BY opts.quantity ASC
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->execute();

		if (empty($data = $dataQuery->fetchAll())) {
			return null;
		}

		if ($isStruct) {
			/** @var ProductToStockRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = (new ProductToStockRowStruct())
					->setProductId((int)Util::getArrItem($item, 'product_id'))
					->setStockId((int)Util::getArrItem($item, 'stock_id'))
					->setQuantity((int)Util::getArrItem($item, 'quantity', ''));
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Return by product and stock ID
	 *
	 * @param int $productId
	 * @param int $stockId
	 * @param bool|null $isStruct
	 * @return ProductToStockRowStruct|mixed|null
	 */
	public function getByProductAndStock(int $productId, int $stockId, bool $isStruct = null) {
		$sql = "
			SELECT
			
				opts.product_id,
				opts.stock_id,
				opts.quantity
			
			FROM oc_product_to_stock opts
			
			WHERE 1
				  AND opts.product_id = :product_id
				  AND opts.stock_id = :stock_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':stock_id', $stockId, \PDO::PARAM_INT);
		$dataQuery->execute();

		if (empty($data = $dataQuery->fetch())) {
			return null;
		}

		if ($isStruct) {
			return (new ProductToStockRowStruct())
				->setProductId((int)Util::getArrItem($data, 'product_id'))
				->setStockId((int)Util::getArrItem($data, 'stock_id'))
				->setQuantity((int)Util::getArrItem($data, 'quantity', ''));
		}

		return $data;
	}

	/**
	 * Return count of product on all stocks
	 *
	 * @param int $productId
	 * @return int
	 */
	public function getCount(int $productId) {
		$count = 0;

		//region Define Models
		$productModel = new Product();
		//endregion

		$list = $this->getListByProduct($productId, true);
		$product = $productModel->get($productId, true);

		if (!empty($product)) {
			$count += $product->getQuantity();
		}

		if (empty($list)) {
			return $count;
		}

		foreach ($list as $item) {
			$count += $item->getQuantity();
		}

		return $count;
	}

	/**
	 * Return product count on stock
	 *
	 * @param int $productId - Product ID
	 * @param int $stockId - Stock ID
	 * @param bool|null $isStruct
	 * @return int
	 */
	public function getCountByProductAndStock(int $productId, int $stockId, bool $isStruct = null) {
		$sql = "
			SELECT
			
				opts.product_id,
				opts.stock_id,
				opts.quantity
			
			FROM oc_product_to_stock opts
			
			WHERE 1
				  AND opts.product_id = :product_id
				  AND opts.stock_id = :stock_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':stock_id', $stockId, \PDO::PARAM_INT);
		$dataQuery->execute();

		if (empty($data = $dataQuery->fetch())) {
			return 0;
		}

		return (int)$data['quantity'];
	}

	/**
	 * Return row with allowed quantity on stock
	 *
	 * @param int $productId
	 * @param int $quantity
	 * @param bool|null $isStruct
	 * @return ProductToStockRowStruct|mixed|null
	 */
	public function getWithAllowedQuantity(int $productId, int $quantity, bool $isStruct = null) {
		$sql = "
			SELECT *
			FROM oc_product_to_stock opts
			WHERE 1
				  AND opts.product_id = :product_id
				  AND opts.quantity >= :quantity
			
			ORDER BY opts.quantity ASC
			
			LIMIT 1
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':quantity', $quantity, \PDO::PARAM_INT);
		$dataQuery->execute();

		if (empty($data = $dataQuery->fetch())) {
			return null;
		}

		if ($isStruct) {
			return (new ProductToStockRowStruct())
				->setProductId((int)Util::getArrItem($data, 'product_id'))
				->setStockId((int)Util::getArrItem($data, 'stock_id'))
				->setQuantity((int)Util::getArrItem($data, 'quantity', ''));
		}

		return $data;
	}

	/**
	 * Return rows products stock ID with available products quantity.
	 *
	 * @param array $productIds
	 * @param int $quantity
	 * @param bool|null $isStruct
	 * @return array|ProductToStockRowStruct[]|null
	 */
	public function getStockWithAvailableProductsQuantity(array $productIds, int $quantity) {
		$strProductIds = join(',', $productIds);

		$sql = "
			SELECT
				
				opts.*,
			    GROUP_CONCAT(opts.product_id) AS products,
				COUNT(1) AS cnt
			
			
			FROM oc_product_to_stock opts
			
			WHERE opts.product_id IN ({$strProductIds})
				AND opts.quantity >= :quantity
			
			GROUP BY opts.stock_id ASC
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':quantity', $quantity, \PDO::PARAM_INT);
		$dataQuery->execute();

		if (empty($data = $dataQuery->fetch())) {
			return null;
		}

		return $data;
	}

	/**
	 * Return rows products stock ID with available products quantity in main stock.
	 *
	 * @param array $productIds
	 * @param int $quantity
	 * @return array|ProductToStockRowStruct[]|null
	 */
	public function getStockWithAvailableProductsQuantityMainStock(array $productIds, int $quantity) {
		$strProductIds = join(',', $productIds);

		$sql = "
			SELECT
				
				op.product_id,
			    0 AS stock_id,
				GROUP_CONCAT(op.product_id) AS products,
				COUNT(1) AS cnt
			
			
			FROM oc_product op
			
			WHERE op.product_id IN ({$strProductIds})
				AND op.quantity >= :quantity
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':quantity', $quantity, \PDO::PARAM_INT);
		$dataQuery->execute();

		if (empty($data = $dataQuery->fetch())) {
			return null;
		}

		return $data;
	}

	/**
	 * Set quantity
	 *
	 * @param ProductToStockRowStruct $row
	 * @return bool
	 */
	public function setQuantity(ProductToStockRowStruct $row) {
		$sql = "
			INSERT INTO oc_product_to_stock (product_id, stock_id, quantity)
			VALUES (:product_id, :stock_id, :quantity)
			ON DUPLICATE KEY UPDATE
				quantity = :quantity
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':quantity', $row->getQuantity(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':product_id', $row->getProductId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':stock_id', $row->getStockId(), \PDO::PARAM_INT);


		return $dataQuery->execute();
	}

	/**
	 * Convert raw data to row struct.
	 *
	 * @param $data
	 * @return ProductToStockRowStruct
	 */
	public function toStruct($data) {
		$data = (array)$data;

		return (new ProductToStockRowStruct())
			->setProductId((int)Util::getArrItem($data, 'product_id'))
			->setStockId((int)Util::getArrItem($data, 'stock_id'))
			->setQuantity((int)Util::getArrItem($data, 'quantity', ''));
	}

}
