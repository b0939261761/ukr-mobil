<?php

namespace Ego\Struct;

class ProductDescriptionRowStruct extends BaseStruct {
	
	/** @var int */
	private $productId;

	/** @var int */
	private $languageId;
	
	/** @var string */
	private $name;

	/** @var string */
	private $description;

	/** @var string */
	private $tag;

	/** @var string */
	private $metaTitle;

	/** @var string */
	private $metaDescription;

	/** @var string */
	private $metaKeyword;

	/**
	 * @return int
	 */
	public function getProductId() {
		return $this->productId;
	}

	/**
	 * @param int $productId
	 * @return ProductDescriptionRowStruct
	 */
	public function setProductId(int $productId): self {
		$this->productId = $productId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLanguageId() {
		return $this->languageId;
	}

	/**
	 * @param int $languageId
	 * @return ProductDescriptionRowStruct
	 */
	public function setLanguageId(int $languageId): self {
		$this->languageId = $languageId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return ProductDescriptionRowStruct
	 */
	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return ProductDescriptionRowStruct
	 */
	public function setDescription(string $description): self {
		$this->description = $description;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTag() {
		return $this->tag;
	}

	/**
	 * @param string $tag
	 * @return ProductDescriptionRowStruct
	 */
	public function setTag(string $tag): self {
		$this->tag = $tag;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMetaTitle() {
		return $this->metaTitle;
	}

	/**
	 * @param string $metaTitle
	 * @return ProductDescriptionRowStruct
	 */
	public function setMetaTitle(string $metaTitle): self {
		$this->metaTitle = $metaTitle;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMetaDescription() {
		return $this->metaDescription;
	}

	/**
	 * @param string $metaDescription
	 * @return ProductDescriptionRowStruct
	 */
	public function setMetaDescription(string $metaDescription): self {
		$this->metaDescription = $metaDescription;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMetaKeyword() {
		return $this->metaKeyword;
	}

	/**
	 * @param string $metaKeyword
	 * @return ProductDescriptionRowStruct
	 */
	public function setMetaKeyword(string $metaKeyword): self {
		$this->metaKeyword = $metaKeyword;

		return $this;
	}
	
}
