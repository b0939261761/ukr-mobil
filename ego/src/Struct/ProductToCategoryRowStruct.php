<?php

namespace Ego\Struct;

class ProductToCategoryRowStruct extends BaseStruct {

	/** @var int */
	private $productId;

	/** @var int */
	private $categoryId;

	/**
	 * @return int
	 */
	public function getProductId() {
		return $this->productId;
	}

	/**
	 * @param int $productId
	 * @return ProductToCategoryRowStruct
	 */
	public function setProductId(int $productId): self {
		$this->productId = $productId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCategoryId() {
		return $this->categoryId;
	}

	/**
	 * @param int $categoryId
	 * @return ProductToCategoryRowStruct
	 */
	public function setCategoryId(int $categoryId): self {
		$this->categoryId = $categoryId;

		return $this;
	}

}
