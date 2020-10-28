<?php
namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\OrderRowStruct;

class Order extends BaseModel {

	public function add(OrderRowStruct $row) {
		$sql = "
			INSERT INTO oc_order
			SET
				customer_id             = :customer_id,
				customer_group_id       = :customer_group_id,
				firstname               = :firstname,
				lastname                = :lastname,
				email                   = :email,
				telephone               = :telephone,
				payment_method          = :payment_method,
				shipping_firstname      = :shipping_firstname,
				shipping_lastname       = :shipping_lastname,
				shipping_telephone      = :shipping_telephone,
				shipping_address_1      = :shipping_address_1,
				shipping_method         = :shipping_method,
				comment                 = :comment,
				total                   = :total,
				date_added              = NOW(),
				date_modified           = NOW(),
				stock_id				        = :stock_id
		";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_id', $row->getCustomerId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':customer_group_id', $row->getCustomerGroupId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':firstname', $row->getFirstName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':lastname', $row->getLastName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':email', $row->getEmail(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':telephone', $row->getTelephone(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':payment_method', $row->getPaymentMethod(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':shipping_firstname', $row->getShippingFirstName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':shipping_lastname', $row->getShippingLastName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':shipping_telephone', $row->getShippingTelephone(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':shipping_address_1', $row->getShippingAddress1(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':shipping_method', $row->getShippingMethod(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':comment', $row->getComment(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':total', $row->getTotal(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':stock_id', $row->getStockId(), \PDO::PARAM_INT);

		$dataQuery->execute();

		return (int)$this->_getDb()->lastInsertId();
	}

	/**
	 * Return order by order ID
	 *
	 * @param int $orderId
	 * @param bool|null $isStruct
	 * @return array|OrderRowStruct|mixed
	 */
	public function get(int $orderId, bool $isStruct = null) {
		$sql = "
			SELECT
				order_id,
				store_id,
        s.name AS store_name,
        os.name AS order_status_name,
				customer_id,
				customer_group_id,
				firstname,
				lastname,
				email,
				telephone,
				payment_method,
				shipping_firstname,
				shipping_lastname,
				shipping_telephone,
				shipping_address_1,
				shipping_method,
        comment,
				total,
				o.order_status_id,
				date_added,
				date_modified,
				stock_id,
        ttn,
        ttn_status
			FROM oc_order o
      LEFT JOIN store s ON s.id = o.store_id
      LEFT JOIN oc_order_status os ON os.order_status_id = o.order_status_id
			WHERE order_id = :order_id
		  ORDER BY order_id DESC
		";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':order_id', $orderId);
		$dataQuery->execute();
		$data = $dataQuery->fetch();

		if (empty($data)) return [];
		if ($isStruct) return $this->toStruct($data);
		return $data;
	}

	public function getByCustomerId(int $customerId, bool $isStruct = null) {
		$sql = "
			SELECT
				order_id,
				store_id,
        s.name AS store_name,
        os.name AS order_status_name,
				customer_id,
				customer_group_id,
				firstname,
				lastname,
				email,
				telephone,
				payment_method,
				shipping_firstname,
				shipping_lastname,
				shipping_telephone,
				shipping_address_1,
				shipping_method,
				comment,
				total,
				o.order_status_id,
				date_added,
				date_modified,
				stock_id,
			  ttn,
			  ttn_status
			FROM oc_order o
      LEFT JOIN store s ON s.id = o.store_id
      LEFT JOIN oc_order_status os ON os.order_status_id = o.order_status_id
      WHERE customer_id = :customer_id
      ORDER BY order_id DESC
		";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_id', $customerId);
		$dataQuery->execute();
		$data = $dataQuery->fetchAll();

		if (empty($data)) return null;

		if ($isStruct) {
			$result = [];
			foreach ($data as $item) $result[] = $this->toStruct($item);
			return $result;
		}

		return $data;
	}

	public function toStruct($data) {
		return (new OrderRowStruct())
			->setOrderId((int)Util::getArrItem($data, 'order_id'))
			->setCustomerId((int)Util::getArrItem($data, 'customer_id'))
			->setCustomerGroupId((int)Util::getArrItem($data, 'customer_group_id'))
			->setFirstName(Util::getArrItem($data, 'firstname'))
			->setLastName(Util::getArrItem($data, 'lastname'))
			->setEmail(Util::getArrItem($data, 'email'))
			->setTelephone(Util::getArrItem($data, 'telephone'))
			->setPaymentMethod(Util::getArrItem($data, 'payment_method'))
			->setShippingFirstName(Util::getArrItem($data, 'shipping_firstname'))
			->setShippingLastName(Util::getArrItem($data, 'shipping_lastname'))
			->setShippingTelephone(Util::getArrItem($data, 'shipping_telephone'))
			->setShippingAddress1(Util::getArrItem($data, 'shipping_address_1'))
			->setShippingMethod(Util::getArrItem($data, 'shipping_method'))
			->setComment(Util::getArrItem($data, 'comment'))
			->setTotal(Util::strToFloat(Util::getArrItem($data, 'total')))
			->setOrderStatusId((int)Util::getArrItem($data, 'order_status_id'))
			->setDateAdded(Util::getArrItem($data, 'date_added'))
			->setDateModified(Util::getArrItem($data, 'date_modified'))
			->setStockId((int)Util::getArrItem($data, 'stock_id'))
			->setTtn(Util::getArrItem($data, 'ttn', ''))
			->setTtnStatus(Util::getArrItem($data, 'ttn_status', ''))
			->setStoreName(Util::getArrItem($data, 'store_name', ''))
			->setOrderStatusName(Util::getArrItem($data, 'order_status_name', ''));
	}
}
