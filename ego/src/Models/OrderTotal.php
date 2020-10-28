<?php

namespace Ego\Models;

use Ego\Struct\OrderTotalRowStruct;

class OrderTotal extends BaseModel {

	/**
	 * Add row
	 *
	 * @param OrderTotalRowStruct $row
	 * @return string
	 */
	public function add(OrderTotalRowStruct $row) {
		$sql = "
			INSERT INTO oc_order_total
			SET
				order_id   = :order_id,
				code       = :code,
				title      = :title,
				value      = :value,
				sort_order = :sort_order
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':order_id', $row->getOrderId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':code', $row->getCode(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':title', $row->getTitle(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':value', $row->getValue(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':sort_order', $row->getSortOrder(), \PDO::PARAM_INT);

		$dataQuery->execute();

		return $this->_getDb()->lastInsertId();
	}

}
