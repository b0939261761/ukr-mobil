<?php

namespace Ego\Struct;

class ProductRowStruct extends BaseStruct {

	/** @var int */
	private $productId;

	/** @var string */
	private $model;

	/** @var string */
	private $sku;

	/** @var string */
	private $upc;

	/** @var string */
	private $ean;

	/** @var string */
	private $jan;

	/** @var string */
	private $isbn;

	/** @var string */
	private $mpn;

	/** @var string */
	private $location;

	/** @var int */
	private $quantity;

	/** @var int */
	private $quantity2;

	/** @var int */
	private $stockStatusId;

	/** @var string */
	private $image;

	/** @var int */
	private $manufacturerId;

	/** @var int */
	private $shipping;

	/** @var float */
	private $price;

	/** @var int */
	private $points;

	/** @var int */
	private $taxClassId;

	/** @var string */
	private $dateAvailable;

	/** @var float */
	private $weight;

	/** @var int */
	private $weightClassId;

	/** @var float */
	private $length;

	/** @var float */
	private $width;

	/** @var float */
	private $height;

	/** @var int */
	private $lengthClassId;

	/** @var int */
	private $subtract;

	/** @var int */
	private $minimum;

	/** @var int */
	private $sortOrder;

	/** @var int */
	private $status;

	/** @var int */
	private $viewed;

	/** @var string */
	private $dateAdded;

	/** @var string */
	private $dateModified;

	/**
	 * @return int
	 */
	public function getProductId() {
		return $this->productId;
	}

	/**
	 * @param int $productId
	 * @return ProductRowStruct
	 */
	public function setProductId(int $productId): self {
		$this->productId = $productId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * @param string $model
	 * @return ProductRowStruct
	 */
	public function setModel(string $model): self {
		$this->model = $model;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSku() {
		return $this->sku;
	}

	/**
	 * @param string $sku
	 * @return ProductRowStruct
	 */
	public function setSku(string $sku): self {
		$this->sku = $sku;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUpc() {
		return $this->upc;
	}

	/**
	 * @param string $upc
	 * @return ProductRowStruct
	 */
	public function setUpc(string $upc): self {
		$this->upc = $upc;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEan() {
		return $this->ean;
	}

	/**
	 * @param string $ean
	 * @return ProductRowStruct
	 */
	public function setEan(string $ean): self {
		$this->ean = $ean;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getJan() {
		return $this->jan;
	}

	/**
	 * @param string $jan
	 * @return ProductRowStruct
	 */
	public function setJan(string $jan): self {
		$this->jan = $jan;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getIsbn() {
		return $this->isbn;
	}

	/**
	 * @param string $isbn
	 * @return ProductRowStruct
	 */
	public function setIsbn(string $isbn): self {
		$this->isbn = $isbn;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMpn() {
		return $this->mpn;
	}

	/**
	 * @param string $mpn
	 * @return ProductRowStruct
	 */
	public function setMpn(string $mpn): self {
		$this->mpn = $mpn;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * @param string $location
	 * @return ProductRowStruct
	 */
	public function setLocation(string $location): self {
		$this->location = $location;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	 * @return int
	 */
	public function getQuantity2() {
		return $this->quantity2;
	}

	/**
	 * @param int $quantity
	 * @return ProductRowStruct
	 */
	public function setQuantity(int $quantity): self {
		$this->quantity = $quantity;

		return $this;
	}

	/**
	 * @param int $quantity
	 * @return ProductRowStruct
	 */
	public function setQuantity2(int $quantity): self {
		$this->quantity2 = $quantity;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getStockStatusId() {
		return $this->stockStatusId;
	}

	/**
	 * @param int $stockStatusId
	 * @return ProductRowStruct
	 */
	public function setStockStatusId(int $stockStatusId): self {
		$this->stockStatusId = $stockStatusId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param string $image
	 * @return ProductRowStruct
	 */
	public function setImage(string $image): self {
		$this->image = $image;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getManufacturerId() {
		return $this->manufacturerId;
	}

	/**
	 * @param int $manufacturerId
	 * @return ProductRowStruct
	 */
	public function setManufacturerId(int $manufacturerId): self {
		$this->manufacturerId = $manufacturerId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getShipping() {
		return $this->shipping;
	}

	/**
	 * @param int $shipping
	 * @return ProductRowStruct
	 */
	public function setShipping(int $shipping): self {
		$this->shipping = $shipping;

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
	 * @return ProductRowStruct
	 */
	public function setPrice(float $price): self {
		$this->price = $price;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPoints() {
		return $this->points;
	}

	/**
	 * @param int $points
	 * @return ProductRowStruct
	 */
	public function setPoints(int $points): self {
		$this->points = $points;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getTaxClassId() {
		return $this->taxClassId;
	}

	/**
	 * @param int $taxClassId
	 * @return ProductRowStruct
	 */
	public function setTaxClassId(int $taxClassId): self {
		$this->taxClassId = $taxClassId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateAvailable() {
		return $this->dateAvailable;
	}

	/**
	 * @param string $dateAvailable
	 * @return ProductRowStruct
	 */
	public function setDateAvailable(string $dateAvailable): self {
		$this->dateAvailable = $dateAvailable;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getWeight() {
		return $this->weight;
	}

	/**
	 * @param float $weight
	 * @return ProductRowStruct
	 */
	public function setWeight(float $weight): self {
		$this->weight = $weight;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getWeightClassId() {
		return $this->weightClassId;
	}

	/**
	 * @param int $weightClassId
	 * @return ProductRowStruct
	 */
	public function setWeightClassId(int $weightClassId): self {
		$this->weightClassId = $weightClassId;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getLength() {
		return $this->length;
	}

	/**
	 * @param float $length
	 * @return ProductRowStruct
	 */
	public function setLength(float $length): self {
		$this->length = $length;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @param float $width
	 * @return ProductRowStruct
	 */
	public function setWidth(float $width): self {
		$this->width = $width;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param float $height
	 * @return ProductRowStruct
	 */
	public function setHeight(float $height): self {
		$this->height = $height;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLengthClassId() {
		return $this->lengthClassId;
	}

	/**
	 * @param int $lengthClassId
	 * @return ProductRowStruct
	 */
	public function setLengthClassId(int $lengthClassId): self {
		$this->lengthClassId = $lengthClassId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSubtract() {
		return $this->subtract;
	}

	/**
	 * @param int $subtract
	 * @return ProductRowStruct
	 */
	public function setSubtract(int $subtract): self {
		$this->subtract = $subtract;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMinimum() {
		return $this->minimum;
	}

	/**
	 * @param int $minimum
	 * @return ProductRowStruct
	 */
	public function setMinimum(int $minimum): self {
		$this->minimum = $minimum;

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
	 * @return ProductRowStruct
	 */
	public function setSortOrder(int $sortOrder): self {
		$this->sortOrder = $sortOrder;

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
	 * @return ProductRowStruct
	 */
	public function setStatus(int $status): self {
		$this->status = $status;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getViewed() {
		return $this->viewed;
	}

	/**
	 * @param int $viewed
	 * @return ProductRowStruct
	 */
	public function setViewed(int $viewed): self {
		$this->viewed = $viewed;

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
	 * @return ProductRowStruct
	 */
	public function setDateAdded(string $dateAdded): self {
		$this->dateAdded = $dateAdded;

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
	 * @return ProductRowStruct
	 */
	public function setDateModified(string $dateModified): self {
		$this->dateModified = $dateModified;
		
		return $this;
	}
	
}
