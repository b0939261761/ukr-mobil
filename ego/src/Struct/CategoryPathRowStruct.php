<?php

namespace Ego\Struct;

class CategoryPathRowStruct extends BaseStruct {

	/** @var int */
	private $categoryId;

	/** @var int */
	private $pathId;

	/** @var int */
	private $level;

	/**
	 * @return int
	 */
	public function getCategoryId() {
		return $this->categoryId;
	}

	/**
	 * @param int $categoryId
	 * @return CategoryPathRowStruct
	 */
	public function setCategoryId(int $categoryId): self {
		$this->categoryId = $categoryId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPathId() {
		return $this->pathId;
	}

	/**
	 * @param int $pathId
	 * @return CategoryPathRowStruct
	 */
	public function setPathId(int $pathId): self {
		$this->pathId = $pathId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * @param int $level
	 * @return CategoryPathRowStruct
	 */
	public function setLevel(int $level): self {
		$this->level = $level;

		return $this;
	}

}
