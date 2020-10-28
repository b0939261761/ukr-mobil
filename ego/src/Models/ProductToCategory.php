<?php

namespace Ego\Models;

class ProductToCategory extends BaseModel {

	/**
	 * Return product category
	 *
	 * @param int $productId
	 * @return bool|int
	 */
	public function getProductCategory(int $productId) {
		$sql = '
			SELECT
				
				ptc.category_id
			
			FROM ' . DB_PREFIX . 'product_to_category ptc
			WHERE ptc.product_id = :product_id
			';

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetch();

		if (empty($data)) {
			return false;
		}

		return (int)$data['category_id'];
	}

}
