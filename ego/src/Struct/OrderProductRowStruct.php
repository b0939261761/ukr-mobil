<?php
namespace Ego\Struct;

class OrderProductRowStruct extends BaseStruct {
  private $orderId = 0;
  private $productId = 0;
  private $quantity = 0;
  private $price = '';
  private $total = '';

  public function getOrderId() {
    return $this->orderId;
  }

  public function setOrderId(int $orderId): self {
    $this->orderId = $orderId;
    return $this;
  }

  public function getProductId() {
    return $this->productId;
  }

  public function setProductId(int $productId): self {
    $this->productId = $productId;
    return $this;
  }

  public function getQuantity() {
    return $this->quantity;
  }

  public function setQuantity(int $quantity): self {
    $this->quantity = $quantity;
    return $this;
  }

  public function getPrice() {
    return $this->price;
  }

  public function setPrice(string $price): self {
    $this->price = $price;
    return $this;
  }

  public function getTotal() {
    return $this->total;
  }

  public function setTotal(string $total): self {
    $this->total = $total;
    return $this;
  }
}
