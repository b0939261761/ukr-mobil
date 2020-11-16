<?
use Ego\Providers\Util;
use Ego\Providers\Validator;

class ControllerCommonFooterNewsletter extends \Ego\Controllers\BaseController {
  public function index() {
    return $this->load->view('common/footer_newsletter');
  }

  /**
   * Subscribe on news
   */
  public function subscribe() {
    $success = false;
    $msg = self::MSG_INTERNAL_ERROR;
    $data = [];

    try {
      //region Input Data
      $transferData = $this->getInput('transferData');
      //endregion

      //region Check required fields is not empty
      if (($errorField = Validator::checkRequiredFields([
        'email'
      ], $transferData))) {
        $description = Util::getArrItem($errorField, 'description', '');

        throw new \RuntimeException("Field '{$description}' must be filled.");
      }
      //endregion

      $email = Util::getArrItem($transferData, 'email.value');

      //region Define Models
      $customerModel = new \Ego\Models\Customer();
      //endregion

      $this->load->model('customer/customer');
      $customer = $this->model_customer_customer->getCustomerByEmail($email);

      if (empty($customer)) {
        $customerRow = (new \Ego\Struct\CustomerRowStruct())
          ->setCustomerGroupId(1)
          ->setStoreId((int)$this->config->get('config_store_id'))
          ->setLanguageId((int)$this->config->get('config_language_id'))
          ->setFirstName($email)
          ->setLastName('')
          ->setEmail($email)
          ->setTelephone('')
          ->setFax('')
          ->setPassword('')
          ->setSalt('')
          ->setCart('')
          ->setWishList('')
          ->setNewsletter(1)
          ->setAddressId(0)
          ->setCustomerField('[]')
          ->setIp('')
          ->setStatus(1)
          ->setSafe(1)
          ->setToken('')
          ->setCode('');

        if (!($customerModel->add($customerRow) > 0)) {
          throw new \Exception("Error occurred while create customer.");
        }
      } else {
        if (!$customerModel->setNewsletter((int)$customer['customer_id'], true)) {
          throw new \Exception("Error occurred while update customer.");
        }
      }

      $success = true;
      $msg = self::MSG_SUCCESS;
    } catch (\Exception $ex) {
      $msg = $ex->getMessage();
    }

    $this->_prepareJson([
      'success' => $success,
      'msg' => $msg,
      'data' => $data
    ]);
  }

}
