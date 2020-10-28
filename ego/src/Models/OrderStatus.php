<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\OrderStatusRowStruct;

class OrderStatus extends BaseModel {

	/**
	 * Get order status by ID and language ID
	 *
	 * @param int $id
	 * @param int $languageId
	 * @param bool|null $isStruct
	 * @return array|OrderStatusRowStruct|null
	 */
	public function get(int $id, int $languageId, bool $isStruct = null) {
		$sql = "
			SELECT
			
				order_status_id,
				language_id,
				name
			
			FROM oc_order_status oc
			
			WHERE 1
				AND oc.order_status_id = :order_status_id
				AND oc.language_id = :language_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':order_status_id', $id, \PDO::PARAM_INT);
		$dataQuery->bindValue(':language_id', $languageId, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetch();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			return (new OrderStatusRowStruct())
				->setOrderStatusId((int)Util::getArrItem($data, 'order_status_id'))
				->setLanguageId((int)Util::getArrItem($data, 'language_id'))
				->setName(Util::getArrItem($data, 'name', ''));
		}

		return $data;
	}

}
