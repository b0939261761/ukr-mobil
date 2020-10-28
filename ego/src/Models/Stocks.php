<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\StockRowStruct;

class Stocks extends BaseModel {

	/**
	 * Add row
	 *
	 * @param StockRowStruct $row
	 * @return string
	 */
	public function add(StockRowStruct $row) {
		$sql = "
			INSERT INTO oc_stocks
			SET
				stock_id = :stock_id,
				name     = :name,
				address  = :address
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':stock_id', $row->getStockId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':name', $row->getName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':address', $row->getAddress(), \PDO::PARAM_STR);
		$dataQuery->execute();

		return $this->_getDb()->lastInsertId();
	}

	/**
	 * Delete row
	 *
	 * @param int $stockId
	 * @return bool
	 */
	public function delete(int $stockId) {
		$sql = "
			DELETE FROM oc_stocks
			WHERE 1
				  AND stock_id = :stock_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':stock_id', $stockId, \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Update row
	 *
	 * @param StockRowStruct $row
	 * @return bool
	 */
	public function update(StockRowStruct $row) {
		$sql = "
			UPDATE oc_stocks
			SET
				name    = :name,
				address = :address
			
			WHERE 1
				  AND stock_id = :stock_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':name', $row->getName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':address', $row->getAddress(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':stock_id', $row->getStockId(), \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Return list
	 *
	 * @param bool|null $isStruct
	 * @return array|StockRowStruct[]|null
	 */
	public function getList(bool $isStruct = null) {
		$sql = "
			SELECT
			
				os.stock_id,
				os.name,
				os.address
			
			FROM oc_stocks os
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->execute();

		if (empty($data = $dataQuery->fetchAll())) {
			return [];
		}

		if ($isStruct) {
			/** @var StockRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = (new StockRowStruct())
					->setStockId((int)Util::getArrItem($item, 'stock_id'))
					->setName(Util::getArrItem($item, 'name', ''))
					->setAddress(Util::getArrItem($item, 'address', ''));
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Return row
	 *
	 * @param int $stockId
	 * @param bool|null $isStruct
	 * @return StockRowStruct|mixed|null
	 */
	public function get(int $stockId, bool $isStruct = null) {
		$sql = "
			SELECT
			
				os.stock_id,
				os.name,
				os.address
			
			FROM oc_stocks os
			
			WHERE 1
				  AND os.stock_id = :stock_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':stock_id', $stockId, \PDO::PARAM_INT);
		$dataQuery->execute();

		if (empty($data = $dataQuery->fetch())) {
			return null;
		}

		if ($isStruct) {
			return (new StockRowStruct())
				->setStockId((int)Util::getArrItem($data, 'stock_id'))
				->setName(Util::getArrItem($data, 'name', ''))
				->setAddress(Util::getArrItem($data, 'address', ''));
		}

		return $data;
	}

}
