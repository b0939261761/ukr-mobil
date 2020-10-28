<?php

namespace Ego\Struct;

class ProductDiscountRowStruct extends BaseStruct {

	/** @var int */
	private $productDiscountId;

	/** @var int */
	private $productId;

	/** @var int */
	private $customerGroupId;

	/** @var int */
	private $quantity;

	/** @var int */
	private $priority;

	/** @var float */
	private $price;

	/** @var string */
	private $dateStart;

	/** @var string */
	private $dateEnd;

	/**
	 * @return int
	 */
	public function getProductDiscountId() {
		return $this->productDiscountId;
	}

	/**
	 * @param int $productDiscountId
	 * @return ProductDiscountRowStruct
	 */
	public function setProductDiscountId(int $productDiscountId): self {
		$this->productDiscountId = $productDiscountId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getProductId() {
		return $this->productId;
	}

	/**
	 * @param int $productId
	 * @return ProductDiscountRowStruct
	 */
	public function setProductId(int $productId): self {
		$this->productId = $productId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCustomerGroupId() {
		return $this->customerGroupId;
	}

	/**
	 * @param int $customerGroupId
	 * @return ProductDiscountRowStruct
	 */
	public function setCustomerGroupId(int $customerGroupId): self {
		$this->customerGroupId = $customerGroupId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	 * @param int $quantity
	 * @return ProductDiscountRowStruct
	 */
	public function setQuantity(int $quantity): self {
		$this->quantity = $quantity;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @param int $priority
	 * @return ProductDiscountRowStruct
	 */
	public function setPriority(int $priority): self {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getPrice() {
		return $this->price;
	}

	/**
	 * @param float $price
	 * @return ProductDiscountRowStruct
	 */
	public function setPrice(float $price): self {
		$this->price = $price;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateStart() {
		return $this->dateStart;
	}

	/**
	 * @param string $dateStart
	 * @return ProductDiscountRowStruct
	 */
	public function setDateStart(string $dateStart): self {
		$this->dateStart = $dateStart;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateEnd() {
		return $this->dateEnd;
	}

	/**
	 * @param string $dateEnd
	 * @return ProductDiscountRowStruct
	 */
	public function setDateEnd(string $dateEnd): self {
		$this->dateEnd = $dateEnd;

		return $this;
	}
	
}
