<?php

namespace Ego\Struct;

class FilterRowStruct extends BaseStruct {

	/** @var int */
	private $filterId;

	/** @var int */
	private $filterGroupId;

	/** @var int */
	private $sortOrder;

	/**
	 * @return int
	 */
	public function getFilterId() {
		return $this->filterId;
	}

	/**
	 * @param int $filterId
	 * @return FilterRowStruct
	 */
	public function setFilterId(int $filterId): self {
		$this->filterId = $filterId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getFilterGroupId() {
		return $this->filterGroupId;
	}

	/**
	 * @param int $filterGroupId
	 * @return FilterRowStruct
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
	 * @return FilterRowStruct
	 */
	public function setSortOrder(int $sortOrder): self {
		$this->sortOrder = $sortOrder;

		return $this;
	}

}
