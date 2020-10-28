<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\FilterDescriptionRowStruct;

class FilterDescription extends BaseModel {

	/**
	 * Return list of filters
	 *
	 * @param int $filterGroupId - Filter group ID
	 * @param int $languageId - Language ID
	 * @param bool|null $isStruct
	 * @return array|FilterDescriptionRowStruct[]
	 */
	public function getList(int $filterGroupId, int $languageId, bool $isStruct = null) {
		$sql = '
			SELECT
				
				fd.*
			
			FROM oc_filter f
				     LEFT JOIN oc_filter_description fd ON fd.filter_id = f.filter_id
			WHERE fd.language_id = :language_id
				AND f.filter_group_id = :filter_group_id
			ORDER BY f.sort_order DESC
			';

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':language_id', $languageId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':filter_group_id', $filterGroupId, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return [];
		}

		if ($isStruct) {
			/** @var FilterDescriptionRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = $this->toStruct($item);
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Return row struct
	 *
	 * @param $data
	 * @return FilterDescriptionRowStruct
	 */
	public function toStruct($data) {
		$data = (array)$data;

		return (new FilterDescriptionRowStruct())
			->setFilterId((int)Util::getArrItem($data, 'filter_id'))
			->setLanguageId((int)Util::getArrItem($data, 'language_id'))
			->setFilterGroupId((int)Util::getArrItem($data, 'filter_group_id'))
			->setName(Util::getArrItem($data, 'name'));
	}

}
