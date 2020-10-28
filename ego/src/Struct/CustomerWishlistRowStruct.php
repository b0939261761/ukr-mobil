<?php

namespace Ego\Struct;

class CustomerWishlistRowStruct extends BaseStruct {

	/** @var int */
	private $customerId;

	/** @var int */
	private $productId;

	/** @var string */
	private $dateAdded;

	/**
	 * @return int
	 */
	public function getCustomerId() {
		return $this->customerId;
	}

	/**
	 * @param int $customerId
	 * @return CustomerWishlistRowStruct
	 */
	public function setCustomerId(int $customerId): self {
		$this->customerId = $customerId;

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
	 * @return CustomerWishlistRowStruct
	 */
	public function setProductId(int $productId): self {
		$this->productId = $productId;

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
	 * @return CustomerWishlistRowStruct
	 */
	public function setDateAdded(string $dateAdded): self {
		$this->dateAdded = $dateAdded;

		return $this;
	}

}
