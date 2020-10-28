<?php
namespace Ego\Models;
use Ego\Struct\OrderProductRowStruct;

class OrderProduct extends BaseModel {
  public function add(OrderProductRowStruct $row) {
    $sql = "
      INSERT INTO oc_order_product
      SET
        order_id   = :order_id,
        product_id = :product_id,
        quantity   = :quantity,
        price      = :price,
        total      = :total
    ";

    $dataQuery = $this->_getDb()->prepare($sql);
    $dataQuery->bindValue(':order_id', $row->getOrderId(), \PDO::PARAM_INT);
    $dataQuery->bindValue(':product_id', $row->getProductId(), \PDO::PARAM_INT);
    $dataQuery->bindValue(':quantity', $row->getQuantity(), \PDO::PARAM_INT);
    $dataQuery->bindValue(':price', $row->getPrice(), \PDO::PARAM_STR);
    $dataQuery->bindValue(':total', $row->getTotal(), \PDO::PARAM_STR);
    $dataQuery->execute();
    return $this->_getDb()->lastInsertId();
  }
}
