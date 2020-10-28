<?php

namespace Ego\Struct;

class EgoPostContentRowStruct extends BaseStruct {

	/** @var int */
	private $id;

	/** @var int */
	private $post;

	/** @var int */
	private $language;

	/** @var string */
	private $title;

	/** @var string */
	private $content;

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return EgoPostContentRowStruct
	 */
	public function setId(int $id): self {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPost(): int {
		return $this->post;
	}

	/**
	 * @param int $post
	 * @return EgoPostContentRowStruct
	 */
	public function setPost(int $post): self {
		$this->post = $post;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @param int $language
	 * @return EgoPostContentRowStruct
	 */
	public function setLanguageId(int $language): self {
		$this->language = $language;

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
	 * @return EgoPostContentRowStruct
	 */
	public function setTitle(string $title): self {
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $content
	 * @return EgoPostContentRowStruct
	 */
	public function setContent(string $content): self {
		$this->content = $content;

		return $this;
	}

}
