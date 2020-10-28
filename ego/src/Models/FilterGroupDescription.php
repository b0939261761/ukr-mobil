<?php

namespace Ego\Models;


use Ego\Providers\Util;
use Ego\Struct\FilterGroupDescriptionRowStruct;

class FilterGroupDescription extends BaseModel {

	/**
	 * Return filter group description list
	 *
	 * @param int $languageId
	 * @param bool|null $isStruct
	 * @return array|FilterGroupDescriptionRowStruct[]
	 */
	public function getList(int $languageId, bool $isStruct = null) {
		$sql = '
			SELECT
				
				fgd.*
			
			FROM oc_filter_group fg
				     LEFT JOIN oc_filter_group_description fgd ON fgd.filter_group_id = fg.filter_group_id
			WHERE fgd.language_id = :language_id
			ORDER BY fg.sort_order DESC
			';

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':language_id', $languageId, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return [];
		}

		if ($isStruct) {
			/** @var FilterGroupDescriptionRowStruct[] $result */
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
	 * @return FilterGroupDescriptionRowStruct
	 */
	public function toStruct($data) {
		$data = (array)$data;

		return (new FilterGroupDescriptionRowStruct())
			->setFilterGroupId((int)Util::getArrItem($data, 'filter_group_id'))
			->setLanguageId((int)Util::getArrItem($data, 'language_id'))
			->setname(Util::getArrItem($data, 'name'));
	}

}
