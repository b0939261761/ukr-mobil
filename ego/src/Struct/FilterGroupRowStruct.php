<?php

namespace Ego\Struct;

class FilterGroupRowStruct extends BaseStruct {

	/** @var int */
	private $filterGroupId;

	/** @var int */
	private $sortOrder;

	/**
	 * @return int
	 */
	public function getFilterGroupId() {
		return $this->filterGroupId;
	}

	/**
	 * @param int $filterGroupId
	 * @return FilterGroupRowStruct
	 */
	public function setFilterGroupId(int $filterGroupId): self {
		$this->filterGroupId = $filterGroupId;

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
	 * @return FilterGroupRowStruct
	 */
	public function setSortOrder(int $sortOrder): self {
		$this->sortOrder = $sortOrder;

		return $this;
	}

}
