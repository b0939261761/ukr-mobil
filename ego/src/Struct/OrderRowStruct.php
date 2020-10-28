<?php

namespace Ego\Struct;

class OrderRowStruct extends BaseStruct {

	/** @var int */
	private $orderId;

	/** @var int */
	private $customerId = 0;

	/** @var int */
	private $customerGroupId = 0;

	/** @var string */
	private $firstName = '';

	/** @var string */
	private $lastName = '';

	/** @var string */
	private $email = '';

	/** @var string */
	private $telephone = '';

	/** @var string */
	private $paymentMethod = '';

	/** @var string */
	private $shippingFirstName = '';

	/** @var string */
	private $shippingLastName = '';

	/** @var string */
	private $shippingTelephone = '';

	/** @var string */
	private $shippingAddress1 = '';

	/** @var string */
	private $shippingMethod = '';

	/** @var string */
	private $comment = '';

	/** @var string */
	private $total = '';

	/** @var int */
	private $orderStatusId = 0;

	/** @var string */
	private $dateAdded = '';

	/** @var string */
	private $dateModified = '';

	/** @var int */
	private $stockId = 0;

	/** @var string */
	private $ttn;

	/** @var string */
	private $ttnStatus;

	/** @var string */
	private $storeName;

	/** @var string */
	private $orderStatusName;

	/**
	 * @return int
	 */
	public function getOrderId() {
		return $this->orderId;
	}

	/**
	 * @param int $orderId
	 * @return OrderRowStruct
	 */
	public function setOrderId(int $orderId): self {
		$this->orderId = $orderId;

		return $this;
	}


	/**
	 * @param string $invoicePrefix
	 * @return OrderRowStruct
	 */
	public function setInvoicePrefix(string $invoicePrefix): self {
		$this->invoicePrefix = $invoicePrefix;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCustomerId() {
		return $this->customerId;
	}

	/**
	 * @param int $customerId
	 * @return OrderRowStruct
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
	 * @return OrderRowStruct
	 */
	public function setCustomerGroupId(int $customerGroupId): self {
		$this->customerGroupId = $customerGroupId;

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
	 * @return OrderRowStruct
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
	 * @return OrderRowStruct
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
	 * @return OrderRowStruct
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
	 * @return OrderRowStruct
	 */
	public function setTelephone(string $telephone): self {
		$this->telephone = $telephone;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPaymentMethod() {
		return $this->paymentMethod;
	}

	/**
	 * @param string $paymentMethod
	 * @return OrderRowStruct
	 */
	public function setPaymentMethod(string $paymentMethod): self {
		$this->paymentMethod = $paymentMethod;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getShippingFirstName() {
		return $this->shippingFirstName;
	}

	/**
	 * @param string $shippingFirstName
	 * @return OrderRowStruct
	 */
	public function setShippingFirstName(string $shippingFirstName): self {
		$this->shippingFirstName = $shippingFirstName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getShippingLastName() {
		return $this->shippingLastName;
	}

	/**
	 * @param string $shippingLastName
	 * @return OrderRowStruct
	 */
	public function setShippingLastName(string $shippingLastName): self {
		$this->shippingLastName = $shippingLastName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getShippingTelephone() {
		return $this->shippingTelephone;
	}

	/**
	 * @param string $shippingTelephone
	 * @return OrderRowStruct
	 */
	public function setShippingTelephone(string $shippingTelephone): self {
		$this->shippingTelephone = $shippingTelephone;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getShippingAddress1() {
		return $this->shippingAddress1;
	}

	/**
	 * @param string $shippingAddress1
	 * @return OrderRowStruct
	 */
	public function setShippingAddress1(string $shippingAddress1): self {
		$this->shippingAddress1 = $shippingAddress1;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getShippingMethod() {
		return $this->shippingMethod;
	}

	/**
	 * @param string $shippingMethod
	 * @return OrderRowStruct
	 */
	public function setShippingMethod(string $shippingMethod): self {
		$this->shippingMethod = $shippingMethod;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * @param string $comment
	 * @return OrderRowStruct
	 */
	public function setComment(string $comment): self {
		$this->comment = $comment;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTotal() {
		return $this->total;
	}

	/**
	 * @param string $total
	 * @return OrderRowStruct
	 */
	public function setTotal(string $total): self {
		$this->total = $total;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getOrderStatusId() {
		return $this->orderStatusId;
	}

	/**
	 * @param int $orderStatusId
	 * @return OrderRowStruct
	 */
	public function setOrderStatusId(int $orderStatusId): self {
		$this->orderStatusId = $orderStatusId;

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
	 * @return OrderRowStruct
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
	 * @return OrderRowStruct
	 */
	public function setDateModified(string $dateModified): self {
		$this->dateModified = $dateModified;

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
	 * @return OrderRowStruct
	 */
	public function setStockId(int $stockId): self {
		$this->stockId = $stockId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTtn() {
		return $this->ttn;
	}

	/**
	 * @param string $ttn
	 * @return OrderRowStruct
	 */
	public function setTtn(string $ttn): self {
		$this->ttn = $ttn;

		return $this;
  }

	public function getTtnStatus() {
		return $this->ttnStatus;
	}

	public function setTtnStatus(string $ttnStatus): self {
		$this->ttnStatus = $ttnStatus;
		return $this;
  }

  public function getStoreName() {
		return $this->storeName;
  }

  public function setStoreName(string $storeName): self {
		$this->storeName = $storeName;
		return $this;
  }
  public function getOrderStatusName() {
		return $this->orderStatusName;
  }

  public function setOrderStatusName(string $orderStatusName): self {
		$this->orderStatusName = $orderStatusName;
		return $this;
  }
}
