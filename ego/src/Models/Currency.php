<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\CurrencyRowStruct;
use Ego\Struct\CustomerRowStruct;

class Currency extends BaseModel {

	/**
	 * Return currency by Code
	 *
	 * @param string $code
	 * @param bool|null $isStruct
	 * @return CurrencyRowStruct|mixed|null
	 */
	public function get(string $code, bool $isStruct = null) {
		$sql = "
			SELECT
			
				oc.currency_id,
				oc.title,
				oc.code,
				oc.symbol_left,
				oc.symbol_right,
				oc.decimal_place,
				oc.value,
				oc.status,
				oc.date_modified
			
			FROM oc_currency oc
			
			WHERE 1
				  AND oc.code = :code
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':code', $code, \PDO::PARAM_STR);
		$dataQuery->execute();

		$data = $dataQuery->fetch();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			return $this->toStruct($data);
		}

		return $data;
	}

	/**
	 * Convert to struct
	 *
	 * @param $data
	 * @return CurrencyRowStruct
	 */
	private function toStruct($data) {
		return (new CurrencyRowStruct())
			->setCurrencyId((int)Util::getArrItem($data, 'currency_id'))
			->setTitle(Util::getArrItem($data, 'title', ''))
			->setCode(Util::getArrItem($data, 'code', ''))
			->setSymbolLeft(Util::getArrItem($data, 'symbol_left', ''))
			->setSymbolRight(Util::getArrItem($data, 'symbol_right', ''))
			->setDecimalPlace(Util::getArrItem($data, 'decimal_place'))
			->setValue(Util::strToFloat(Util::getArrItem($data, 'value')))
			->setStatus((int)Util::getArrItem($data, 'status'))
			->setDateModified(Util::getArrItem($data, 'date_modified', ''));
	}

}
