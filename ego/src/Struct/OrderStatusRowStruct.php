<?php

namespace Ego\Struct;

class OrderStatusRowStruct extends BaseStruct {

	/** @var int */
	private $orderStatusId;

	/** @var int */
	private $languageId;

	/** @var string */
	private $name;

	/**
	 * @return int
	 */
	public function getOrderStatusId() {
		return $this->orderStatusId;
	}

	/**
	 * @param int $orderStatusId
	 * @return OrderStatusRowStruct
	 */
	public function setOrderStatusId(int $orderStatusId): self {
		$this->orderStatusId = $orderStatusId;

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
	 * @return OrderStatusRowStruct
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
	 * @return OrderStatusRowStruct
	 */
	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

}
