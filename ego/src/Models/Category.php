<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\CategoryRowStruct;

class Category extends BaseModel {

	/**
	 * Return category by ID
	 *
	 * @param int $categoryId
	 * @param bool|null $isStruct
	 * @return CategoryRowStruct|mixed|null
	 */
	public function get($categoryId, bool $isStruct = null) {
		$sql = "
			SELECT
				
				c.category_id,
				c.image,
				c.parent_id,
				c.top,
				c.column,
				c.sort_order,
				c.status,
				c.date_added,
				c.date_modified
			
			FROM oc_category c
			WHERE c.category_id = :category_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category_id', $categoryId);
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
	 * Return category by ID
	 *
	 * @param int $childCategoryId
	 * @param bool|null $isStruct
	 * @return CategoryRowStruct|mixed|null
	 */
	public function getParent($childCategoryId, bool $isStruct = null) {
		$sql = "
			SELECT
				
				parent.category_id,
				parent.image,
				parent.parent_id,
				parent.top,
				parent.column,
				parent.sort_order,
				parent.status,
				parent.date_added,
				parent.date_modified
			
			FROM oc_category c
				     LEFT JOIN oc_category parent ON parent.category_id = c.parent_id
			WHERE c.category_id = :category_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category_id', $childCategoryId);
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
	 * @param $data
	 * @return CategoryRowStruct
	 */
	public function toStruct($data) {
		$data = (array)$data;

		return (new CategoryRowStruct())
			->setCategoryId((int)Util::getArrItem($data, 'category_id'))
			->setImage(Util::getArrItem($data, 'image', ''))
			->setParentId((int)Util::getArrItem($data, 'parent_id'))
			->setTop((int)Util::getArrItem($data, 'top'))
			->setColumn((int)Util::getArrItem($data, 'column'))
			->setSortOrder((int)Util::getArrItem($data, 'sort_order'))
			->setStatus((int)Util::getArrItem($data, 'status'))
			->setDateAdded(Util::getArrItem($data, 'date_added', ''))
			->setDateModified(Util::getArrItem($data, 'date_modified', ''));
	}

}
