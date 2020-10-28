<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\EgoPostContentRowStruct;

class EgoPostContent extends BaseModel {

	/**
	 * Return multi language data
	 *
	 * @param int $post
	 * @param int|null $lang
	 * @param bool|null $isGroup
	 * @param bool|null $isStruct
	 * @return array|EgoPostContentRowStruct[]|null
	 */
	public function getByPost(int $post, int $lang = null, bool $isGroup = null, bool $isStruct = null) {
		$where = 'WHERE 1 ';

		if ($lang > 0) {
			$where .= "AND epc.epc_language = {$lang}";
		}

		$group = '';

		if ($isGroup) {
			$group = "GROUP BY epc.epc_post ";
		}

		$sql = "
			SELECT
			
				epc.epc_id       AS id,
				epc.epc_post     AS post,
				epc.epc_language AS language,
				epc.epc_title    AS title,
				epc.epc_content  AS content
			
			FROM ego_post_content epc
			{$where}
				  AND epc_post = :post
				  
			{$group}
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':post', $post, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			/** @var EgoPostContentRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = (new EgoPostContentRowStruct())
					->setId((int)Util::getArrItem($item, 'id'))
					->setPost((int)Util::getArrItem($item, 'post'))
					->setLanguageId((int)Util::getArrItem($item, 'language'))
					->setTitle(Util::getArrItem($item, 'title'))
					->setContent(Util::getArrItem($item, 'content'));
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Add row
	 *
	 * @param EgoPostContentRowStruct $row
	 * @return bool
	 */
	public function add(EgoPostContentRowStruct $row) {
		$sql = "
			INSERT INTO ego_post_content
			SET
				epc_post     = :post,
				epc_language = :language,
				epc_title    = :title,
				epc_content  = :content
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':post', $row->getPost(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':language', $row->getLanguage(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':title', $row->getTitle(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':content', $row->getContent(), \PDO::PARAM_STR);
		$dataQuery->execute();

		return $this->_getDb()->lastInsertId();
	}

	/**
	 * Update row
	 *
	 * @param EgoPostContentRowStruct $row
	 * @return bool
	 */
	public function update(EgoPostContentRowStruct $row) {
		$sql = "
			UPDATE ego_post_content
			SET
				epc_title   = :title,
				epc_content = :content
			
			WHERE 1
				  AND epc_post = :post
				  AND epc_language = :language
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':title', $row->getTitle(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':content', $row->getContent(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':post', $row->getPost(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':language', $row->getLanguage(), \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Check is post content exists
	 *
	 * @param int $postId
	 * @param int $lang
	 * @return bool
	 */
	public function isExists(int $postId, int $lang) {
		$sql = "
			SELECT count(1) AS count
			FROM ego_post_content epc
			WHERE 1
				  AND epc.epc_post = :post
				  AND epc.epc_language = :language
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':post', $postId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':language', $lang, \PDO::PARAM_INT);
		$dataQuery->execute();

		return (int)$dataQuery->fetch()['count'] > 0;
	}

}
