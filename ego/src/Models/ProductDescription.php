<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\OrderRowStruct;
use Ego\Struct\ProductDescriptionRowStruct;
use Ego\Struct\ProductRowStruct;

class ProductDescription extends BaseModel {

	/**
	 * Return product description by product ID
	 *
	 * @param int $productId
	 * @param int $lang
	 * @param bool|null $isStruct
	 * @return array|ProductDescriptionRowStruct|null
	 */
	public function get(int $productId, int $lang, bool $isStruct = null) {
		$sql = "
			SELECT

				pd.product_id,
				pd.language_id,
				pd.name,
				pd.description,
				pd.tag,
				pd.meta_title,
				pd.meta_description,
				pd.meta_keyword
			
			FROM oc_product_description pd
			
			WHERE 1
				  AND pd.product_id = :product_id
				  AND pd.language_id = :language_id
	 		 ";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':product_id', $productId, \PDO::PARAM_INT);
		$dataQuery->bindValue(':language_id', $lang, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetch();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			return (new ProductDescriptionRowStruct())
				->setProductId((int)Util::getArrItem($data, 'product_id', ''))
				->setLanguageId((int)Util::getArrItem($data, 'language_id', ''))
				->setName(Util::getArrItem($data, 'name', ''))
				->setDescription(Util::getArrItem($data, 'description', ''))
				->setTag(Util::getArrItem($data, 'tag', ''))
				->setMetaTitle(Util::getArrItem($data, 'meta_title', ''))
				->setMetaDescription(Util::getArrItem($data, 'meta_description', ''))
				->setMetaKeyword(Util::getArrItem($data, 'meta_keyword', ''));
		}

		return $data;
	}

}
