<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\CustomerWishlistRowStruct;

class CustomerWishlist extends BaseModel {

	/**
	 * Return all wishlist
	 *
	 * @param bool|null $isStruct
	 * @return array|CustomerWishlistRowStruct[]
	 */
	public function getAll(bool $isStruct = null) {
		$sql = '
			SELECT
				
				cw.customer_id,
				cw.product_id,
				cw.date_added
			
			FROM oc_customer_wishlist cw
			';

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return [];
		}

		if ($isStruct) {
			/** @var CustomerWishlistRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = $this->toStruct($item);
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Check existing product in wishlist
	 *
	 * @param int $customerId
	 * @param int $productId
	 * @return bool
	 */
	public function exist(int $customerId, int $productId) {
		$sql = '
			SELECT
				COUNT(1) AS cnt
			FROM oc_customer_wishlist
			WHERE customer_id = :customer_id
				AND product_id = :product_id
			';

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_id', $customerId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);

		$dataQuery->execute();

		return (int)$dataQuery->fetch()['cnt'] > 0;
	}

	/**
	 * Add row
	 *
	 * @param CustomerWishlistRowStruct $row
	 * @return bool
	 */
	public function add(CustomerWishlistRowStruct $row) {
		$sql = '
			INSERT INTO oc_customer_wishlist
			SET customer_id = :customer_id,
				product_id  = :product_id,
				date_added  = NOW()
			';

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_id', $row->getCustomerId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':product_id', $row->getProductId(), \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Remove item by customer and product ID
	 *
	 * @param int $customerId
	 * @param int $productId
	 * @return bool
	 */
	public function remove(int $customerId, int $productId) {
		$sql = "
			DELETE
			FROM oc_customer_wishlist
			WHERE customer_id = :customer_id
				AND product_id = :product_id 
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_id', $customerId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Return row struct
	 *
	 * @param $data
	 * @return CustomerWishlistRowStruct
	 */
	public function toStruct($data) {
		$data = (array)$data;

		return (new CustomerWishlistRowStruct())
			->setCustomerId((int)Util::getArrItem($data, 'customer_id'))
			->setProductId((int)Util::getArrItem($data, 'product_id'))
			->setDateAdded(Util::getArrItem($data, 'date_added'));
	}

}
