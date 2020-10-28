<?php

namespace Ego\Struct;

class CategoryDescriptionRowStruct extends BaseStruct {
	
	/** @var int */
	private $categoryId;

	/** @var int */
	private $languageId;
	
	/** @var string */
	private $name;

	/** @var string */
	private $description;

	/** @var string */
	private $metaTitle;

	/** @var string */
	private $metaDescription;

	/** @var string */
	private $metaKeyword;

	/**
	 * @return int
	 */
	public function getCategoryId() {
		return $this->categoryId;
	}

	/**
	 * @param int $categoryId
	 * @return CategoryDescriptionRowStruct
	 */
	public function setCategoryId(int $categoryId): self {
		$this->categoryId = $categoryId;

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
	 * @return CategoryDescriptionRowStruct
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
	 * @return CategoryDescriptionRowStruct
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
	 * @return CategoryDescriptionRowStruct
	 */
	public function setDescription(string $description): self {
		$this->description = $description;

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
	 * @return CategoryDescriptionRowStruct
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
	 * @return CategoryDescriptionRowStruct
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
	 * @return CategoryDescriptionRowStruct
	 */
	public function setMetaKeyword(string $metaKeyword): self {
		$this->metaKeyword = $metaKeyword;
		
		return $this;
	}
	
}
