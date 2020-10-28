<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\OrderRowStruct;
use Ego\Struct\ProductDescriptionRowStruct;
use Ego\Struct\ProductDiscountRowStruct;
use Ego\Struct\ProductRowStruct;

class ProductDiscount extends BaseModel {

	/**
	 * Return product discount by product and customer group ID
	 *
	 * @param int $productId
	 * @param int $customerGroupId
	 * @param bool|null $isStruct
	 * @return ProductDiscountRowStruct|mixed|null
	 */
	public function getProductAndGroup(int $productId, int $customerGroupId, bool $isStruct = null) {
		$sql = "
			SELECT
			
				pd.product_discount_id,
				pd.product_id,
				pd.customer_group_id,
				pd.quantity,
				pd.priority,
				pd.price,
				pd.date_start,
				pd.date_end
			
			FROM oc_product_discount pd
			
			WHERE 1
				  AND pd.product_id = :product_id
				  AND pd.customer_group_id = :customer_group_id
	 		";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':customer_group_id', $customerGroupId, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetch();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			return (new ProductDiscountRowStruct())
				->setProductDiscountId((int)Util::getArrItem($data, 'product_discount_id', ''))
				->setProductId((int)Util::getArrItem($data, 'product_id', ''))
				->setCustomerGroupId((int)Util::getArrItem($data, 'customer_group_id', ''))
				->setQuantity((int)Util::getArrItem($data, 'quantity', ''))
				->setPrice(Util::strToFloat(Util::getArrItem($data, 'price', '')))
				->setDateStart(Util::getArrItem($data, 'date_start', ''))
				->setDateEnd(Util::getArrItem($data, 'date_end', ''));
		}

		return $data;
	}

}
