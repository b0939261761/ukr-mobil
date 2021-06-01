<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\EgoPostRowStruct;

class EgoPost extends BaseModel {

	/**
	 * Return list of post
	 *
	 * @param bool|null $isStruct
	 * @return array|EgoPostRowStruct[]|null
	 */
	public function getList(bool $isStruct = null) {
		$sql = "
			SELECT

				ep.ep_id            AS id,
				ep.ep_category      AS category,
				ep.ep_preview_image AS preview_image,
				ep.ep_date_create   AS date_create,
				ep.ep_date_update   AS date_update

			FROM ego_post ep
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			/** @var EgoPostRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = (new EgoPostRowStruct())
					->setId((int)Util::getArrItem($item, 'id'))
					->setCategory(Util::getArrItem($item, 'category', ''))
					->setPreviewImage(Util::getArrItem($item, 'preview_image', ''))
					->setDateCreate(Util::getArrItem($item, 'date_create', ''))
					->setDateUpdate(Util::getArrItem($item, 'date_update', ''));
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Return post by ID
	 *
	 * @param int $id
	 * @param bool|null $isStruct
	 * @return EgoPostRowStruct|mixed|null
	 */
	public function get(int $id, bool $isStruct = null) {
		$sql = "
			SELECT

				ep.ep_id            AS id,
				ep.ep_category      AS category,
				ep.ep_preview_image AS preview_image,
        productId1,
        productId2,
        productId3,
        productId4,
        productId5,
        description,
				ep.ep_date_create   AS date_create,
				ep.ep_date_update   AS date_update

			FROM ego_post ep
			WHERE 1
					AND ep.ep_id = :id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':id', $id, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetch();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			return(new EgoPostRowStruct())
				->setId((int)Util::getArrItem($data, 'id'))
				->setCategory(Util::getArrItem($data, 'category', ''))
				->setPreviewImage(Util::getArrItem($data, 'preview_image', ''))
				->setProductId1((int)Util::getArrItem($data, 'productId1', 0))
				->setProductId2((int)Util::getArrItem($data, 'productId2', 0))
				->setProductId3((int)Util::getArrItem($data, 'productId3', 0))
				->setProductId4((int)Util::getArrItem($data, 'productId4', 0))
				->setProductId5((int)Util::getArrItem($data, 'productId5', 0))
				->setDescription(Util::getArrItem($data, 'description', ''))
				->setDateCreate(Util::getArrItem($data, 'date_create', ''))
				->setDateUpdate(Util::getArrItem($data, 'date_update', ''));
		}

		return $data;
	}

	/**
	 * Return list by category name
	 *
	 * @param string $category
	 * @param bool|null $isStruct
	 * @return array|EgoPostRowStruct[]|null
	 */
	public function getByCategory(string $category, bool $isStruct = null) {
		$sql = "
			SELECT

				ep.ep_id            AS id,
				ep.ep_category      AS category,
				ep.ep_preview_image AS preview_image,
				ep.ep_date_create   AS date_create,
				ep.ep_date_update   AS date_update

			FROM ego_post ep

			WHERE 1
					AND ep.ep_category = :category

			ORDER BY id DESC
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category', $category, \PDO::PARAM_STR);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			/** @var EgoPostRowStruct[] $result */
			$result = [];

			foreach ($data as $item) {
				$result[] = (new EgoPostRowStruct())
					->setId((int)Util::getArrItem($item, 'id'))
					->setCategory(Util::getArrItem($item, 'category', ''))
					->setPreviewImage(Util::getArrItem($item, 'preview_image', ''))
					->setDateCreate(Util::getArrItem($item, 'date_create', ''))
					->setDateUpdate(Util::getArrItem($item, 'date_update', ''));
			}

			return $result;
		}

		return $data;
	}

	/**
	 * Delete post by ID
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id) {
		$sql = "
			DELETE FROM ego_post
			WHERE ep_id = :id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':id', $id, \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Add row
	 *
	 * @param EgoPostRowStruct $row
	 * @return string
	 */
	public function add(EgoPostRowStruct $row) {
		$sql = "
			INSERT INTO ego_post
			SET
				ep_category    = :category,
				ep_preview_image = :preview_image,
				ep_date_create = NOW(),
				ep_date_update = NOW()
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category', $row->getCategory(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':preview_image', $row->getPreviewImage(), \PDO::PARAM_STR);
		$dataQuery->execute();

		return $this->_getDb()->lastInsertId();
	}

	/**
	 * Update row
	 *
	 * @param EgoPostRowStruct $row
	 * @return bool
	 */
	public function update(EgoPostRowStruct $row) {
		$sql = "
			UPDATE ego_post
			SET
				ep_category      = :category,
				ep_preview_image = :preview_image,
        productId1       = :productId1,
        productId2       = :productId2,
        productId3       = :productId3,
        productId4       = :productId4,
        productId5       = :productId5,
        description      = :description,
				ep_date_update   = NOW()

			WHERE ep_id = :post
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':category', $row->getCategory(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':preview_image', $row->getPreviewImage(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':post', $row->getId(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':productId1', $row->getProductId1(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':productId2', $row->getProductId2(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':productId3', $row->getProductId3(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':productId4', $row->getProductId4(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':productId5', $row->getProductId5(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':description', $row->getDescription(), \PDO::PARAM_STR);

		return $dataQuery->execute();
	}

}
