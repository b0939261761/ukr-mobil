<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\OrderRowStruct;
use Ego\Struct\ProductRowStruct;
use Ego\Struct\ProductSpecialRowStruct;

class Product extends BaseModel {

	/**
	 * Return products by category ID
	 *
	 * @param int $categoryId
	 * @param bool|null $isStruct
	 * @return array|ProductRowStruct[]|null
	 */
	public function getByCategoryId(int $categoryId, bool $isStruct = null) {
		$sql = "
			SELECT
			
				p.product_id,
				p.model,
				p.sku,
				p.upc,
				p.ean,
				p.jan,
				p.isbn,
				p.mpn,
				p.location,
				p.quantity,
				p.stock_status_id,
				p.image,
				p.manufacturer_id,
				p.shipping,
				p.price,
				p.points,
				p.tax_class_id,
				p.date_available,
				p.weight,
				p.weight_class_id,
				p.length,
				p.width,
				p.height,
				p.length_class_id,
				p.subtract,
				p.minimum,
				p.sort_order,
				p.status,
				p.viewed,
				p.date_added,
				p.date_modified
			
			FROM oc_product_to_category ptc
				LEFT JOIN oc_product p ON p.product_id = ptc.product_id
			
			WHERE 1
					AND ptc.category_id = :category_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			/** @var ProductRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = (new ProductRowStruct())
					->setProductId((int)Util::getArrItem($item, 'product_id', ''))
					->setModel(Util::getArrItem($item, 'model', ''))
					->setQuantity((int)Util::getArrItem($item, 'quantity', ''))
					->setManufacturerId((int)Util::getArrItem($item, 'manufacturer_id', ''))
					->setShipping((int)Util::getArrItem($item, 'shipping', ''))
					->setPrice(Util::strToFloat(Util::getArrItem($item, 'price', '')))
					->setPoints((int)Util::getArrItem($item, 'points', ''))
					->setTaxClassId((int)Util::getArrItem($item, 'tax_class_id', ''))
					->setDateAvailable(Util::getArrItem($item, 'date_available', ''))
					->setWeight(Util::strToFloat(Util::getArrItem($item, 'weight', '')))
					->setWeightClassId((int)Util::getArrItem($item, 'weight_class_id', ''))
					->setLength(Util::strToFloat(Util::getArrItem($item, 'length', '')))
					->setWidth(Util::strToFloat(Util::getArrItem($item, 'width', '')))
					->setHeight(Util::strToFloat(Util::getArrItem($item, 'height', '')))
					->setLengthClassId((int)Util::getArrItem($item, 'length_class_id', ''))
					->setSubtract((int)Util::getArrItem($item, 'subtract', ''))
					->setMinimum((int)Util::getArrItem($item, 'minimum', ''))
					->setSortOrder((int)Util::getArrItem($item, 'sort_order', ''))
					->setStatus((int)Util::getArrItem($item, 'status', ''))
					->setViewed((int)Util::getArrItem($item, 'viewed', ''))
					->setDateAdded(Util::getArrItem($item, 'date_added', ''))
					->setDateModified(Util::getArrItem($item, 'date_modified', ''));
			}

			return $result;
		}

		return $data;
	}

	public function getByCategoryIdForPrice(int $categoryId) {
		$sql = "
			SELECT
				p.product_id,
				p.quantity,
				pts.quantity AS quantity2,
				p.price,
				p.status
			
			FROM oc_product_to_category ptc
				LEFT JOIN oc_product p ON p.product_id = ptc.product_id
				LEFT JOIN oc_product_description pd ON pd.product_id = ptc.product_id
				LEFT JOIN oc_product_to_stock pts ON pts.product_id = ptc.product_id
			
			WHERE ptc.category_id = :category_id
					AND pd.`language_id` = 2
			ORDER BY pd.name
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		
		$result = [];

		foreach ($data as $item) {
			$result[] = (new ProductRowStruct())
				->setProductId((int)Util::getArrItem($item, 'product_id', ''))
				->setQuantity((int)Util::getArrItem($item, 'quantity', ''))
				->setQuantity2((int)Util::getArrItem($item, 'quantity2', ''))
				->setPrice(Util::strToFloat(Util::getArrItem($item, 'price', '')))
				->setStatus((int)Util::getArrItem($item, 'status', ''));
		}

		return $result;
	}

	/**
	 * Return row by product ID
	 *
	 * @param int $productId
	 * @param bool|null $isStruct
	 * @return ProductRowStruct|mixed|null
	 */
	public function get(int $productId, bool $isStruct = null) {
		$sql = "
			SELECT
			
				p.product_id,
				p.model,
				p.sku,
				p.upc,
				p.ean,
				p.jan,
				p.isbn,
				p.mpn,
				p.location,
				p.quantity,
				p.stock_status_id,
				p.image,
				p.manufacturer_id,
				p.shipping,
				p.price,
				p.points,
				p.tax_class_id,
				p.date_available,
				p.weight,
				p.weight_class_id,
				p.length,
				p.width,
				p.height,
				p.length_class_id,
				p.subtract,
				p.minimum,
				p.sort_order,
				p.status,
				p.viewed,
				p.date_added,
				p.date_modified
			
			FROM oc_product_to_category ptc
				LEFT JOIN oc_product p ON p.product_id = ptc.product_id
			
			WHERE 1
					AND ptc.product_id = :product_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetch();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			return (new ProductRowStruct())
				->setProductId((int)Util::getArrItem($data, 'product_id', ''))
				->setModel(Util::getArrItem($data, 'model', ''))
				->setSku(Util::getArrItem($data, 'sku', ''))
				->setUpc(Util::getArrItem($data, 'upc', ''))
				->setEan(Util::getArrItem($data, 'ean', ''))
				->setJan(Util::getArrItem($data, 'jan', ''))
				->setIsbn(Util::getArrItem($data, 'isbn', ''))
				->setMpn(Util::getArrItem($data, 'mpn', ''))
				->setLocation(Util::getArrItem($data, 'location', ''))
				->setQuantity((int)Util::getArrItem($data, 'quantity', ''))
				->setStockStatusId((int)Util::getArrItem($data, 'stock_status_id', ''))
				->setImage(Util::getArrItem($data, 'image', ''))
				->setManufacturerId((int)Util::getArrItem($data, 'manufacturer_id', ''))
				->setShipping((int)Util::getArrItem($data, 'shipping', ''))
				->setPrice(Util::strToFloat(Util::getArrItem($data, 'price', '')))
				->setPoints((int)Util::getArrItem($data, 'points', ''))
				->setTaxClassId((int)Util::getArrItem($data, 'tax_class_id', ''))
				->setDateAvailable(Util::getArrItem($data, 'date_available', ''))
				->setWeight(Util::strToFloat(Util::getArrItem($data, 'weight', '')))
				->setWeightClassId((int)Util::getArrItem($data, 'weight_class_id', ''))
				->setLength(Util::strToFloat(Util::getArrItem($data, 'length', '')))
				->setWidth(Util::strToFloat(Util::getArrItem($data, 'width', '')))
				->setHeight(Util::strToFloat(Util::getArrItem($data, 'height', '')))
				->setLengthClassId((int)Util::getArrItem($data, 'length_class_id', ''))
				->setSubtract((int)Util::getArrItem($data, 'subtract', ''))
				->setMinimum((int)Util::getArrItem($data, 'minimum', ''))
				->setSortOrder((int)Util::getArrItem($data, 'sort_order', ''))
				->setStatus((int)Util::getArrItem($data, 'status', ''))
				->setViewed((int)Util::getArrItem($data, 'viewed', ''))
				->setDateAdded(Util::getArrItem($data, 'date_added', ''))
				->setDateModified(Util::getArrItem($data, 'date_modified', ''));
		}

		return $data;
	}

	/**
	 * Update product quantity
	 *
	 * @param int $productId
	 * @param int $quantity
	 * @return bool
	 */
	public function setQuantity(int $productId, int $quantity) {
		$sql = "
			UPDATE oc_product
			SET
				quantity = :quantity
			
			WHERE 1
				  AND product_id = :product_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':quantity', $quantity, \PDO::PARAM_INT);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Return list of new product
	 *
	 * @param int $limit
	 * @param bool|null $isStruct
	 * @return array|ProductRowStruct[]|mixed|null
	 */
	public function getNewList(int $limit = 10, bool $isStruct = null) {
		$sql = "
			SELECT *
			FROM oc_product oc
			WHERE oc.status = 1
			ORDER BY oc.date_added DESC
			LIMIT {$limit}
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->execute();
		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			/** @var ProductRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = $this->toStruct($item);
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Return list of new product
	 *
	 * @param int $limit
	 * @param int $customerGroupId
	 * @param bool|null $isStruct
	 * @return array|ProductSpecialRowStruct[]|null
	 */
	public function getStocks(int $limit, int $customerGroupId, bool $isStruct = null) {
		$sql = "
			SELECT *
			FROM oc_product_special opc
			WHERE opc.customer_group_id = :customer_group_id
				AND (NOW() >= opc.date_start OR opc.date_start IS NULL OR opc.date_start = '0000-00-00')
				AND (NOW() <=opc.date_end OR opc.date_end IS NULL OR opc.date_end = '0000-00-00')
			ORDER BY opc.date_start DESC
			LIMIT {$limit}
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_group_id', $customerGroupId);
		$dataQuery->execute();
		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			/** @var ProductSpecialRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = $this->toStructSpecial($item);
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Return special product by ID
	 *
	 * @param int $productId
	 * @param int $customerGroupId
	 * @param bool|null $isStruct
	 * @return ProductSpecialRowStruct|mixed|null
	 */
	public function getStock(int $productId, int $customerGroupId, bool $isStruct = null) {
		$sql = "
			SELECT *
			FROM oc_product_special opc
			WHERE opc.product_id = :product_id
				AND opc.customer_group_id = :customer_group_id
			ORDER BY opc.date_start DESC
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId);
		$dataQuery->bindValue(':customer_group_id', $customerGroupId);
		$dataQuery->execute();
		$data = $dataQuery->fetch();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			return $this->toStructSpecial($data);
		}

		return $data;
	}

	/**
	 * Convert row data to row struct
	 *
	 * @param $data
	 * @return ProductRowStruct
	 */
	public function toStruct($data) {
		$data = (array)$data;

		return (new ProductRowStruct())
			->setProductId((int)Util::getArrItem($data, 'product_id', ''))
			->setModel(Util::getArrItem($data, 'model', ''))
			->setSku(Util::getArrItem($data, 'sku', ''))
			->setUpc(Util::getArrItem($data, 'upc', ''))
			->setEan(Util::getArrItem($data, 'ean', ''))
			->setJan(Util::getArrItem($data, 'jan', ''))
			->setIsbn(Util::getArrItem($data, 'isbn', ''))
			->setMpn(Util::getArrItem($data, 'mpn', ''))
			->setLocation(Util::getArrItem($data, 'location', ''))
			->setQuantity((int)Util::getArrItem($data, 'quantity', ''))
			->setStockStatusId((int)Util::getArrItem($data, 'stock_status_id', ''))
			->setImage(Util::getArrItem($data, 'image', ''))
			->setManufacturerId((int)Util::getArrItem($data, 'manufacturer_id', ''))
			->setShipping((int)Util::getArrItem($data, 'shipping', ''))
			->setPrice(Util::strToFloat(Util::getArrItem($data, 'price', '')))
			->setPoints((int)Util::getArrItem($data, 'points', ''))
			->setTaxClassId((int)Util::getArrItem($data, 'tax_class_id', ''))
			->setDateAvailable(Util::getArrItem($data, 'date_available', ''))
			->setWeight(Util::strToFloat(Util::getArrItem($data, 'weight', '')))
			->setWeightClassId((int)Util::getArrItem($data, 'weight_class_id', ''))
			->setLength(Util::strToFloat(Util::getArrItem($data, 'length', '')))
			->setWidth(Util::strToFloat(Util::getArrItem($data, 'width', '')))
			->setHeight(Util::strToFloat(Util::getArrItem($data, 'height', '')))
			->setLengthClassId((int)Util::getArrItem($data, 'length_class_id', ''))
			->setSubtract((int)Util::getArrItem($data, 'subtract', ''))
			->setMinimum((int)Util::getArrItem($data, 'minimum', ''))
			->setSortOrder((int)Util::getArrItem($data, 'sort_order', ''))
			->setStatus((int)Util::getArrItem($data, 'status', ''))
			->setViewed((int)Util::getArrItem($data, 'viewed', ''))
			->setDateAdded(Util::getArrItem($data, 'date_added', ''))
			->setDateModified(Util::getArrItem($data, 'date_modified', ''));
	}

	/**
	 * Convert row data to row struct
	 *
	 * @param $data
	 * @return ProductSpecialRowStruct
	 */
	public function toStructSpecial($data) {
		$data = (array)$data;

		return (new ProductSpecialRowStruct())
			->setProductSpecialId((int)Util::getArrItem($data, 'product_special_id'))
			->setProductId((int)Util::getArrItem($data, 'product_id'))
			->setCustomerGroupId((int)Util::getArrItem($data, 'customer_group_id'))
			->setPriority((int)Util::getArrItem($data, 'priority'))
			->setPrice(Util::strToFloat(Util::getArrItem($data, 'price')))
			->setDateStart(Util::getArrItem($data, 'date_start', ''))
			->setDateEnd(Util::getArrItem($data, 'data_end', ''));
	}




	/**
	 * Return max product price at all or in certain category
	 *
	 * @param int|null $categoryId - Category ID
	 * @return float
	 */
	public function getMaxPrice(int $categoryId = null) {
		if ($categoryId > 0) {
			$sql = '
				SELECT
					p.price
				FROM oc_category_path cp
					     LEFT JOIN oc_product_to_category ptc ON ptc.category_id = cp.category_id
					     LEFT JOIN oc_product p ON p.product_id = ptc.product_id
				WHERE cp.path_id = 10000003
				ORDER BY p.price DESC
				LIMIT 1
				';

			$dataQuery = $this->_getDb()->prepare($sql);
			$dataQuery->bindValue(':path_id', $categoryId, \PDO::PARAM_INT);
		} else {
			$sql = '
				SELECT
					p.price
				FROM oc_product p
				ORDER BY p.price DESC
				LIMIT 1
			';

			$dataQuery = $this->_getDb()->prepare($sql);
		}

		$dataQuery->execute();

		return Util::strToFloat($dataQuery->fetch()['price']);
	}


	public function getBrands(int $categoryId) {
		$sql = "
			SELECT
				distinct b.id,
				b.name
			FROM oc_category_path cp
			LEFT JOIN oc_product_to_category ptc ON ptc.category_id = cp.category_id
			LEFT JOIN oc_product p ON p.product_id = ptc.product_id
			LEFT JOIN products_models pm ON pm.product_id = p.product_id
			LEFT JOIN models m ON m.id = pm.model_id
			INNER JOIN brands b ON b.id = m.brand_id
			WHERE cp.path_id = :path_id
			ORDER BY b.ord, b.name
		";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':path_id', $categoryId, \PDO::PARAM_INT);
		$dataQuery->execute();

		return $dataQuery->fetch();
	}
}
