<?php

namespace Ego\Struct;

class ProductToStockRowStruct extends BaseStruct {
	
	/** @var int */
	private $productId;

	/** @var int */
	private $stockId;

	/** @var int */
	private $quantity;

	/**
	 * @return int
	 */
	public function getProductId() {
		return $this->productId;
	}

	/**
	 * @param int $productId
	 * @return ProductToStockRowStruct
	 */
	public function setProductId(int $productId): self {
		$this->productId = $productId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getStockId() {
		return $this->stockId;
	}

	/**
	 * @param int $stockId
	 * @return ProductToStockRowStruct
	 */
	public function setStockId(int $stockId): self {
		$this->stockId = $stockId;

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
	 * @return ProductToStockRowStruct
	 */
	public function setQuantity(int $quantity): self {
		$this->quantity = $quantity;

		return $this;
	}
	
}
