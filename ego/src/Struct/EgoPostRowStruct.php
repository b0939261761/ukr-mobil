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

}
