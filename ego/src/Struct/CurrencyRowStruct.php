<?php

namespace Ego\Struct;

class CurrencyRowStruct extends BaseStruct {

	/** @var int */
	private $currencyId;

	/** @var string */
	private $title;

	/** @var string */
	private $code;

	/** @var string */
	private $symbolLeft;

	/** @var string */
	private $symbolRight;

	/** @var string */
	private $decimalPlace;

	/** @var float */
	private $value;

	/** @var int */
	private $status;

	/** @var string */
	private $dateModified;

	/**
	 * @return int
	 */
	public function getCurrencyId() {
		return $this->currencyId;
	}

	/**
	 * @param int $currencyId
	 * @return CurrencyRowStruct
	 */
	public function setCurrencyId(int $currencyId): self {
		$this->currencyId = $currencyId;

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
	 * @return CurrencyRowStruct
	 */
	public function setTitle(string $title): self {
		$this->title = $title;

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
	 * @return CurrencyRowStruct
	 */
	public function setCode(string $code): self {
		$this->code = $code;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSymbolLeft() {
		return $this->symbolLeft;
	}

	/**
	 * @param string $symbolLeft
	 * @return CurrencyRowStruct
	 */
	public function setSymbolLeft(string $symbolLeft): self {
		$this->symbolLeft = $symbolLeft;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSymbolRight() {
		return $this->symbolRight;
	}

	/**
	 * @param string $symbolRight
	 * @return CurrencyRowStruct
	 */
	public function setSymbolRight(string $symbolRight): self {
		$this->symbolRight = $symbolRight;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDecimalPlace() {
		return $this->decimalPlace;
	}

	/**
	 * @param string $decimalPlace
	 * @return CurrencyRowStruct
	 */
	public function setDecimalPlace(string $decimalPlace): self {
		$this->decimalPlace = $decimalPlace;

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
	 * @return CurrencyRowStruct
	 */
	public function setValue(float $value): self {
		$this->value = $value;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param int $status
	 * @return CurrencyRowStruct
	 */
	public function setStatus(int $status): self {
		$this->status = $status;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateModified() {
		return $this->dateModified;
	}

	/**
	 * @param string $dateModified
	 * @return CurrencyRowStruct
	 */
	public function setDateModified(string $dateModified): self {
		$this->dateModified = $dateModified;

		return $this;
	}

}
