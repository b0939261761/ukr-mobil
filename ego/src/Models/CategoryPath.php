<?php

namespace Ego\Models;

class CategoryPath extends BaseModel {

	/**
	 * Return max level for category
	 *
	 * @param $categoryId
	 * @return int
	 */
	public function getLevel($categoryId) {
		$sql = '
			SELECT
				
				MAX(cp.level) AS level
			
			FROM oc_category_path cp
			WHERE cp.category_id = 10000005
			';

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
		$dataQuery->execute();

		return (int)$dataQuery->fetch()['level'];
	}

	/**
	 * Return root category ID
	 * s
	 * @param $categoryId
	 * @return int
	 */
	public function getRoot($categoryId) {
		$sql = '
			SELECT
				
			    category_id,
			    path_id,
				MAX(level) as level
			
			FROM oc_category_path
			WHERE category_id = :category_id
			';

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
		$dataQuery->execute();

		return (int)$dataQuery->fetch()['path_id'];
	}

}
