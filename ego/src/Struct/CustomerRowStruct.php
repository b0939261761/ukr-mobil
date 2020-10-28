<?php

namespace Ego\Struct;

class CustomerRowStruct extends BaseStruct {

	/** @var int */
	private $customerId;

	/** @var int */
	private $customerGroupId;

	/** @var int */
	private $storeId;

	/** @var int */
	private $languageId;

	/** @var string */
	private $firstName;

	/** @var string */
	private $lastName;

	/** @var string */
	private $email;

	/** @var string */
	private $telephone;

	/** @var string */
	private $fax;

	/** @var string */
	private $password;

	/** @var string */
	private $salt;

	/** @var string */
	private $cart;

	/** @var string */
	private $wishList;

	/** @var int */
	private $newsletter;

	/** @var int */
	private $addressId;

	/** @var string */
	private $region;

	/** @var string */
	private $city;

	/** @var string */
	private $warehouse;

	/** @var string */
	private $customerField;

	/** @var string */
	private $ip;

	/** @var int */
	private $status;

	/** @var int */
	private $safe;

	/** @var string */
	private $token;

	/** @var string */
	private $code;

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
	 * @return CustomerRowStruct
	 */
	public function setCustomerId(int $customerId): self {
		$this->customerId = $customerId;

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
	 * @return CustomerRowStruct
	 */
	public function setCustomerGroupId(int $customerGroupId): self {
		$this->customerGroupId = $customerGroupId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getStoreId() {
		return $this->storeId;
	}

	/**
	 * @param int $storeId
	 * @return CustomerRowStruct
	 */
	public function setStoreId(int $storeId): self {
		$this->storeId = $storeId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLanguageId() {
		return $this->languageId;
	}

	/**
	 * @param int $languageId
	 * @return CustomerRowStruct
	 */
	public function setLanguageId(int $languageId): self {
		$this->languageId = $languageId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @param string $firstName
	 * @return CustomerRowStruct
	 */
	public function setFirstName(string $firstName): self {
		$this->firstName = $firstName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * @param string $lastName
	 * @return CustomerRowStruct
	 */
	public function setLastName(string $lastName): self {
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param string $email
	 * @return CustomerRowStruct
	 */
	public function setEmail(string $email): self {
		$this->email = $email;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTelephone() {
		return $this->telephone;
	}

	/**
	 * @param string $telephone
	 * @return CustomerRowStruct
	 */
	public function setTelephone(string $telephone): self {
		$this->telephone = $telephone;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFax() {
		return $this->fax;
	}

	/**
	 * @param string $fax
	 * @return CustomerRowStruct
	 */
	public function setFax(string $fax): self {
		$this->fax = $fax;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return CustomerRowStruct
	 */
	public function setPassword(string $password): self {
		$this->password = $password;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSalt() {
		return $this->salt;
	}

	/**
	 * @param string $salt
	 * @return CustomerRowStruct
	 */
	public function setSalt(string $salt): self {
		$this->salt = $salt;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCart() {
		return $this->cart;
	}

	/**
	 * @param string $cart
	 * @return CustomerRowStruct
	 */
	public function setCart(string $cart): self {
		$this->cart = $cart;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getWishList() {
		return $this->wishList;
	}

	/**
	 * @param string $wishList
	 * @return CustomerRowStruct
	 */
	public function setWishList(string $wishList): self {
		$this->wishList = $wishList;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getNewsletter() {
		return $this->newsletter;
	}

	/**
	 * @param int $newsletter
	 * @return CustomerRowStruct
	 */
	public function setNewsletter(int $newsletter): self {
		$this->newsletter = $newsletter;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getAddressId() {
		return $this->addressId;
	}

	/**
	 * @param int $addressId
	 * @return CustomerRowStruct
	 */
	public function setAddressId(int $addressId): self {
		$this->addressId = $addressId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * @param string $region
	 * @return CustomerRowStruct
	 */
	public function setRegion(string $region): self {
		$this->region = $region;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @param string $city
	 * @return CustomerRowStruct
	 */
	public function setCity(string $city): self {
		$this->city = $city;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getWarehouse() {
		return $this->warehouse;
	}

	/**
	 * @param string $warehouse
	 * @return CustomerRowStruct
	 */
	public function setWarehouse(string $warehouse): self {
		$this->warehouse = $warehouse;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCustomerField() {
		return $this->customerField;
	}

	/**
	 * @param string $customerField
	 * @return CustomerRowStruct
	 */
	public function setCustomerField(string $customerField): self {
		$this->customerField = $customerField;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getIp() {
		return $this->ip;
	}

	/**
	 * @param string $ip
	 * @return CustomerRowStruct
	 */
	public function setIp(string $ip): self {
		$this->ip = $ip;

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
	 * @return CustomerRowStruct
	 */
	public function setStatus(int $status): self {
		$this->status = $status;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSafe() {
		return $this->safe;
	}

	/**
	 * @param int $safe
	 * @return CustomerRowStruct
	 */
	public function setSafe(int $safe): self {
		$this->safe = $safe;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * @param string $token
	 * @return CustomerRowStruct
	 */
	public function setToken(string $token): self {
		$this->token = $token;

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
	 * @return CustomerRowStruct
	 */
	public function setCode(string $code): self {
		$this->code = $code;

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
	 * @return CustomerRowStruct
	 */
	public function setDateAdded(string $dateAdded): self {
		$this->dateAdded = $dateAdded;

		return $this;
	}

}
