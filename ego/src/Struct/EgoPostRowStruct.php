<?php

namespace Ego\Struct;

class EgoPostRowStruct extends BaseStruct {

	/** @var int */
	private $id;

	/** @var string */
	private $category;

	/** @var string */
	private $previewImage;

	/** @var string */
	private $dateCreate;

	/** @var string */
	private $dateUpdate;

	private $productId1;
	private $productId2;
	private $productId3;
	private $productId4;
	private $productId5;
	private $description;

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return EgoPostRowStruct
	 */
	public function setId(int $id): self {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @param string $category
	 * @return EgoPostRowStruct
	 */
	public function setCategory(string $category): self {
		$this->category = $category;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPreviewImage() {
		return $this->previewImage;
	}

	/**
	 * @param string $previewImage
	 * @return EgoPostRowStruct
	 */
	public function setPreviewImage(string $previewImage): self {
		$this->previewImage = $previewImage;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateCreate() {
		return $this->dateCreate;
	}

	/**
	 * @param string $dateCreate
	 * @return EgoPostRowStruct
	 */
	public function setDateCreate(string $dateCreate): self {
		$this->dateCreate = $dateCreate;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateUpdate() {
		return $this->dateUpdate;
	}

	/**
	 * @param string $dateUpdate
	 * @return EgoPostRowStruct
	 */
	public function setDateUpdate(string $dateUpdate): self {
		$this->dateUpdate = $dateUpdate;

		return $this;
	}

	public function getProductId1() {
		return $this->productId1;
	}

	public function setProductId1($productId): self {
		$this->productId1 = $productId;
		return $this;
	}

  public function getProductId2() {
		return $this->productId2;
	}

	public function setProductId2($productId): self {
		$this->productId2 = $productId;
		return $this;
	}

  public function getProductId3() {
		return $this->productId3;
	}

	public function setProductId3($productId): self {
		$this->productId3 = $productId;
		return $this;
	}

  public function getProductId4() {
		return $this->productId4;
	}

	public function setProductId4($productId): self {
		$this->productId4 = $productId;
		return $this;
	}

  public function getProductId5() {
		return $this->productId5;
	}

	public function setProductId5($productId): self {
		$this->productId5 = $productId;
		return $this;
	}

  public function getDescription() {
		return $this->description;
	}

	public function setDescription($description): self {
		$this->description = $description;
		return $this;
	}

}
