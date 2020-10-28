<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\CategoryDescriptionRowStruct;
use Ego\Struct\OrderRowStruct;

class CategoryDescription extends BaseModel {

	/**
	 * Return `Category Description` list
	 *
	 * @param int $lang
	 * @param bool|null $isStruct
	 * @return array|CategoryDescriptionRowStruct[]|null
	 */
	public function getList(int $lang, bool $isStruct = null) {
		$sql = "
			SELECT
			
				cd.category_id,
				cd.language_id,
				cd.name,
				cd.description,
				cd.meta_title,
				cd.meta_description,
				cd.meta_keyword
			
			FROM oc_category_description cd
			
			WHERE 1
				  AND cd.language_id = :language_id
			ORDER BY category_id ASC";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':language_id', $lang, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			/** @var CategoryDescriptionRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = (new CategoryDescriptionRowStruct())
					->setCategoryId((int)Util::getArrItem($item, 'category_id'))
					->setLanguageId((int)Util::getArrItem($item, 'language_id'))
					->setName(Util::getArrItem($item, 'name', ''))
					->setDescription(Util::getArrItem($item, 'description', ''))
					->setMetaTitle(Util::getArrItem($item, 'meta_title', ''))
					->setDescription(Util::getArrItem($item, 'meta_description', ''))
					->setMetaKeyword(Util::getArrItem($item, 'meta_keyword', ''));
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Return category list by name
	 *
	 * @param $categoryName
	 * @param bool|null $isStruct
	 * @return array|CategoryDescriptionRowStruct[]|null
	 */
	public function getByName($categoryName, bool $isStruct = null) {
		$sql = "
			SELECT
				
				cd.category_id,
				cd.language_id,
				cd.name,
				cd.description,
				cd.meta_title,
				cd.meta_description,
				cd.meta_keyword
			
			FROM oc_category_description cd
			WHERE LOWER(cd.name) LIKE '%{$categoryName}%'
			GROUP BY LOWER(cd.name)
			ORDER BY LOWER(cd.name) ASC
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->execute();
		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			/** @var CategoryDescriptionRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = $this->toStruct($item);
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Return by category ID and language
	 *
	 * @param $categoryId
	 * @param $lang
	 * @param bool|null $isStruct
	 * @return array|CategoryDescriptionRowStruct|null
	 */
	public function get($categoryId, int $lang, bool $isStruct = null) {
		$sql = "
			SELECT
				
				cd.category_id,
				cd.language_id,
				cd.name,
				cd.description,
				cd.meta_title,
				cd.meta_description,
				cd.meta_keyword
			
			FROM oc_category_description cd
			WHERE cd.category_id = :category_id
				  AND cd.language_id = :language_id
			GROUP BY LOWER(cd.name)
			ORDER BY LOWER(cd.name) ASC
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':language_id', $lang, \PDO::PARAM_INT);
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
	 * Convert object to row struct
	 *
	 * @param $data - Input data row
	 * @return CategoryDescriptionRowStruct
	 */
	private function toStruct($data) {
		$data = (array)$data;

		return (new CategoryDescriptionRowStruct())
			->setCategoryId((int)Util::getArrItem($data, 'category_id'))
			->setLanguageId((int)Util::getArrItem($data, 'language_id'))
			->setName(Util::getArrItem($data, 'name', ''))
			->setDescription(Util::getArrItem($data, 'description', ''))
			->setMetaTitle(Util::getArrItem($data, 'meta_title', ''))
			->setDescription(Util::getArrItem($data, 'meta_description', ''))
			->setMetaKeyword(Util::getArrItem($data, 'meta_keyword', ''));
	}

}
