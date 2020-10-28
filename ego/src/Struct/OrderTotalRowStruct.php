<?php

namespace Ego\Struct;

class OrderTotalRowStruct extends BaseStruct {

	/** @var int */
	private $orderTotalId;

	/** @var int */
	private $orderId;

	/** @var string */
	private $code = 'total';

	/** @var string */
	private $title = 'Сумма';

	/** @var float */
	private $value;

	/** @var int */
	private $sortOrder = 1;

	/**
	 * @return int
	 */
	public function getOrderTotalId() {
		return $this->orderTotalId;
	}

	/**
	 * @param int $orderTotalId
	 * @return OrderTotalRowStruct
	 */
	public function setOrderTotalId(int $orderTotalId): self {
		$this->orderTotalId = $orderTotalId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getOrderId() {
		return $this->orderId;
	}

	/**
	 * @param int $orderId
	 * @return OrderTotalRowStruct
	 */
	public function setOrderId(int $orderId): self {
		$this->orderId = $orderId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @param string $code
	 * @return OrderTotalRowStruct
	 */
	public function setCode(string $code): self {
		$this->code = $code;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return OrderTotalRowStruct
	 */
	public function setTitle(string $title): self {
		$this->title = $title;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param float $value
	 * @return OrderTotalRowStruct
	 */
	public function setValue(float $value): self {
		$this->value = $value;

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
	 * @return OrderTotalRowStruct
	 */
	public function setSortOrder(int $sortOrder): self {
		$this->sortOrder = $sortOrder;

		return $this;
	}

}
