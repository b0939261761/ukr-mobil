<?php

namespace Ego\Struct;

class ProductSpecialRowStruct extends BaseStruct {

	/** @var int */
	private $productSpecialId;

	/** @var int */
	private $productId;

	/** @var int */
	private $customerGroupId;

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
	public function getProductSpecialId() {
		return $this->productSpecialId;
	}

	/**
	 * @param int $productSpecialId
	 * @return ProductSpecialRowStruct
	 */
	public function setProductSpecialId(int $productSpecialId): self  {
		$this->productSpecialId = $productSpecialId;

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
	 * @return ProductSpecialRowStruct
	 */
	public function setProductId(int $productId): self  {
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
	 * @return ProductSpecialRowStruct
	 */
	public function setCustomerGroupId(int $customerGroupId): self  {
		$this->customerGroupId = $customerGroupId;

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
	 * @return ProductSpecialRowStruct
	 */
	public function setPriority(int $priority): self  {
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
	 * @return ProductSpecialRowStruct
	 */
	public function setPrice(float $price): self  {
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
	 * @return ProductSpecialRowStruct
	 */
	public function setDateStart(string $dateStart): self  {
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
	 * @return ProductSpecialRowStruct
	 */
	public function setDateEnd(string $dateEnd): self  {
		$this->dateEnd = $dateEnd;

		return $this;
	}

}
