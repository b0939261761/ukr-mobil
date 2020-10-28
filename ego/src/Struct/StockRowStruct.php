<?php

namespace Ego\Struct;

class StockRowStruct extends BaseStruct {
	
	/** @var int */
	private $stockId;
	
	/** @var string */
	private $name;
	
	/** @var string */
	private $address;

	/**
	 * @return int
	 */
	public function getStockId() {
		return $this->stockId;
	}

	/**
	 * @param int $stockId
	 * @return StockRowStruct
	 */
	public function setStockId(int $stockId): self {
		$this->stockId = $stockId;

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
	 * @return StockRowStruct
	 */
	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * @param string $address
	 * @return StockRowStruct
	 */
	public function setAddress(string $address): self {
		$this->address = $address;

		return $this;
	}
	
}
