<?php

namespace Ego\Struct;

class FilterGroupDescriptionRowStruct extends BaseStruct {

	/** @var int */
	private $filterGroupId;

	/** @var int */
	private $languageId;

	/** @var string */
	private $name;

	/**
	 * @return int
	 */
	public function getFilterGroupId() {
		return $this->filterGroupId;
	}

	/**
	 * @param int $filterGroupId
	 * @return FilterGroupDescriptionRowStruct
	 */
	public function setFilterGroupId(int $filterGroupId): self {
		$this->filterGroupId = $filterGroupId;

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
	 * @return FilterGroupDescriptionRowStruct
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
	 * @return FilterGroupDescriptionRowStruct
	 */
	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

}
