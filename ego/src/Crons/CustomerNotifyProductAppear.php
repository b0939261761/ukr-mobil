<?php
namespace Ego\Crons;

use Ego\Models\BaseModel;
use Ego\Providers\MailProvider;

class CustomerNotifyProductAppear extends BaseCron {
  private $db = null;

  private function getCustomerWishlist() {
    $sql = "
      SELECT
        cw.customer_id,
        cw.product_id,
        oc.email,
        pd.name
      FROM oc_customer_wishlist cw
      LEFT JOIN oc_customer oc ON oc.customer_id = cw.customer_id
      LEFT JOIN oc_product p ON p.product_id = cw.product_id
      LEFT JOIN oc_product_description pd ON pd.product_id = cw.product_id
      WHERE pd.language_id = 2
        AND p.quantity + p.quantity_store_2 > 0
    ";

		$dataQuery = $this->db->prepare($sql);
		$dataQuery->execute();
    return $dataQuery->fetchAll();
  }

  private function removeCustomerWish($customerId, $productId) {
    $sql = "
      DELETE
      FROM oc_customer_wishlist
      WHERE customer_id = {$customerId}
        AND product_id = {$productId}
    ";
    $dataQuery = $this->db->prepare($sql);
    return $dataQuery->execute();
  }

	protected function _execute() {
    $configService = new \Ego\Services\ConfigService();
    $emailAdministratorMain = $configService->getEmailAdministratorMain();
    $siteTitle = $configService->getSiteTitle();
    $this->db = (new BaseModel())->_getDb();

		foreach ($this->getCustomerWishlist() as $wishProduct) {
      $customerId = $wishProduct['customer_id'];
      $productId = $wishProduct['product_id'];
      $email = $wishProduct['email'];
      $name = $wishProduct['name'];

      if (empty($email) || empty($name)) {
        $this->removeCustomerWish($customerId, $productId);
        continue;
      }

     	try {
        (new MailProvider())
          ->setTo($email)
          ->setFrom($emailAdministratorMain, $siteTitle)
          ->setSubject('Товар у нас, спешите купить!')
          ->setView('mails.customer-notify-product-appear')
          ->setBodyData([
            'header-title' => 'Товар у нас, спешите купить!',
            'product' => $name
        ])
        ->sendMail();

        $this->removeCustomerWish($customerId, $productId);
			} catch (\Exception $ex) {
				echo $ex->getMessage() . '<br>';
      }
    }

    $this->db = null; // Закрываем соединение к DB
	}
}
