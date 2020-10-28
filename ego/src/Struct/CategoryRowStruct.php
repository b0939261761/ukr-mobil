<?php

namespace Ego\Struct;

class CategoryRowStruct extends BaseStruct {

	/** @var int */
	private $categoryId;

	/** @var string */
	private $image;

	/** @var int */
	private $parent_id;

	/** @var int */
	private $top;

	/** @var int */
	private $column;

	/** @var int */
	private $sortOrder;

	/** @var int */
	private $status;

	/** @var string */
	private $dateAdded;

	/** @var string */
	private $dateModified;

	/**
	 * @return int
	 */
	public function getCategoryId() {
		return $this->categoryId;
	}

	/**
	 * @param int $categoryId
	 * @return CategoryRowStruct
	 */
	public function setCategoryId(int $categoryId): self {
		$this->categoryId = $categoryId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param string $image
	 * @return CategoryRowStruct
	 */
	public function setImage(string $image): self {
		$this->image = $image;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getParentId() {
		return $this->parent_id;
	}

	/**
	 * @param int $parent_id
	 * @return CategoryRowStruct
	 */
	public function setParentId(int $parent_id): self {
		$this->parent_id = $parent_id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getTop() {
		return $this->top;
	}

	/**
	 * @param int $top
	 * @return CategoryRowStruct
	 */
	public function setTop(int $top): self {
		$this->top = $top;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getColumn() {
		return $this->column;
	}

	/**
	 * @param int $column
	 * @return CategoryRowStruct
	 */
	public function setColumn(int $column): self {
		$this->column = $column;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSortOrder() {
		return $this->sortOrder;
	}

	/**
	 * @param int $sortOrder
	 * @return CategoryRowStruct
	 */
	public function setSortOrder(int $sortOrder): self {
		$this->sortOrder = $sortOrder;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param int $status
	 * @return CategoryRowStruct
	 */
	public function setStatus(int $status): self {
		$this->status = $status;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateAdded() {
		return $this->dateAdded;
	}

	/**
	 * @param string $dateAdded
	 * @return CategoryRowStruct
	 */
	public function setDateAdded(string $dateAdded): self {
		$this->dateAdded = $dateAdded;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateModified() {
		return $this->dateModified;
	}

	/**
	 * @param string $dateModified
	 * @return CategoryRowStruct
	 */
	public function setDateModified(string $dateModified): self {
		$this->dateModified = $dateModified;

		return $this;
	}

}
