<?php

namespace Ego\Struct;

class FilterDescriptionRowStruct extends BaseStruct {

	/** @var int */
	private $filterId;

	/** @var int */
	private $languageId;

	/** @var int */
	private $filterGroupId;

	/** @var string */
	private $name;

	/**
	 * @return int
	 */
	public function getFilterId() {
		return $this->filterId;
	}

	/**
	 * @param int $filterId
	 * @return FilterDescriptionRowStruct
	 */
	public function setFilterId(int $filterId): self {
		$this->filterId = $filterId;

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
	 * @return FilterDescriptionRowStruct
	 */
	public function setLanguageId(int $languageId): self {
		$this->languageId = $languageId;

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
	 * @return FilterDescriptionRowStruct
	 */
	public function setFilterGroupId(int $filterGroupId): self {
		$this->filterGroupId = $filterGroupId;

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
	 * @return FilterDescriptionRowStruct
	 */
	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

}
