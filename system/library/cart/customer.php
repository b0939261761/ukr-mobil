<?php
namespace Cart;
class Customer {
  private $id;
  private $firstName;
  private $lastName;
  private $groupId = 1;
  private $email;
  private $phone;
  private $isNewsLetter;

  public function __construct($registry) {
    $this->config = $registry->get('config');
    $this->db = $registry->get('db');
    $this->request = $registry->get('request');
    $this->session = $registry->get('session');

    $this->db->query("DELETE FROM oc_cart WHERE customer_id = 0 AND TIMESTAMPDIFF(HOUR, date_added, NOW()) > 1");
    $this->db->query("DELETE FROM oc_customer_login WHERE TIMESTAMPDIFF(DAY, date_modified, NOW()) > 1");

    $customerId = (int)($this->session->data['customerId'] ?? 0);
    if (!$customerId) return;
    $sql = "
      SELECT
        customer_id AS id,
        firstname AS firstName,
        lastname AS lastName,
        customer_group_id AS groupId,
        email,
        SUBSTRING(telephone, 4, 9) AS phone,
        newsletter AS isNewsLetter
      FROM oc_customer
      WHERE customer_id = {$customerId} AND status = 1
    ";

    $customer = $this->db->query($sql)->row;
    if (empty($customer)) {
      unset($this->session->data['customerId']);
      return;
    }
    $this->initFields($customer);
    $this->updateCart();
  }

  private function updateCart() {
    $sessionId = $this->db->escape($this->session->getId());
    $customerId = $this->getId();

    $sql = "UPDATE oc_cart SET session_id = '{$sessionId}' WHERE customer_id = {$customerId}";
    $this->db->query($sql);

    $sql = "
      INSERT INTO oc_cart (session_id, customer_id, product_id, quantity)
        SELECT * FROM (
          SELECT session_id, {$customerId}, product_id, quantity AS newQuantity
            FROM oc_cart WHERE customer_id = 0 AND session_id = '{$sessionId}'
        ) t
        ON DUPLICATE KEY UPDATE quantity = quantity + newQuantity
    ";

    $this->db->query($sql);

    $sql = "DELETE FROM oc_cart WHERE customer_id = 0 AND session_id = '{$sessionId}'";
    $this->db->query($sql);
  }

  private function initFields($customer) {
    $this->id = (int)($customer['id'] ?? 0);
    $this->firstName = $customer['firstName'];
    $this->lastName = $customer['lastName'] ?? '';
    $this->groupId = $customer['groupId'] ?? 1;
    $this->email = $customer['email'] ?? '';
    $this->phone = $customer['phone'] ?? '';
    $this->isNewsLetter = $customer['isNewsLetter'] ?? false;
  }

  public function reset() {
    unset($this->session->data['customerId']);
    $this->initFields(null);
  }

  public function getId() {
    return $this->id;
  }

  public function getFirstName() {
    return $this->firstName;
  }

  public function getLastName() {
    return $this->lastName;
  }

  public function getGroupId() {
    return $this->groupId;
  }

  public function getEmail() {
    return $this->email;
  }

  public function setEmail($email) {
    $this->email = $email;
    return $this;
  }

  public function getPhone() {
    return $this->phone;
  }

  public function setPhone($phone) {
    $this->phone = $phone;
    return $this;
  }

  public function getIsNewsletter() {
    return $this->isNewsLetter;
  }

  // public function getAddressId() {
  //   return $this->address_id;
  // }

  // public function getBalance() {
  //   $query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$this->customer_id . "'");

  //   return $query->row['total'];
  // }

  // public function getRewardPoints() {
  //   $query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$this->customer_id . "'");
  //   return $query->row['total'];
  // }
}
