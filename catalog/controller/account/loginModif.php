<?
// use Ego\Controllers\BaseController;

class ControllerAccountLogin extends Controller {
  private $error = [];

  public function index() {
    $this->load->model('account/customer');

    file_put_contents('./catalog/controller/account/__LOG__.txt', "-----------\n", FILE_APPEND);

    if (!empty($this->request->get['token'])) {
      $this->customer->logout();
      $this->cart->clear();

      // unset($this->session->data['order_id']);
      // unset($this->session->data['payment_address']);
      // unset($this->session->data['payment_method']);
      // unset($this->session->data['payment_methods']);
      // unset($this->session->data['shipping_address']);
      // unset($this->session->data['shipping_method']);
      // unset($this->session->data['shipping_methods']);
      // unset($this->session->data['comment']);
      // unset($this->session->data['coupon']);
      // unset($this->session->data['reward']);
      // unset($this->session->data['voucher']);
      // unset($this->session->data['vouchers']);

      $customer_info = $this->model_account_customer->getCustomerByToken($this->request->get['token']);

      if ($customer_info && $this->customer->login($customer_info['email'], '', true)) {
        file_put_contents('./catalog/controller/account/__LOG__.txt', "-----------\n" . json_encode($customer_info)."\n\n", FILE_APPEND);
        // // Default Addresses
        // $this->load->model('account/address');

        // if ($this->config->get('config_tax_customer') == 'payment') {
          //   $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
        // }

        // if ($this->config->get('config_tax_customer') == 'shipping') {
        //   $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
        // }

        $this->response->redirect($this->url->link('account/account'));
      }
    }

    if ($this->customer->isLogged()) $this->response->redirect($this->url->link('account/account'));


    $this->load->language('account/login');

    $this->document->setTitle($this->language->get('heading_title'));
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
      $this->response->redirect($this->url->link('account/account'));

    //   // Unset guest
    //   unset($this->session->data['guest']);

    //   // Default Shipping Address
    //   $this->load->model('account/address');

    //   if ($this->config->get('config_tax_customer') == 'payment') {
    //     $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
    //   }

    //   if ($this->config->get('config_tax_customer') == 'shipping') {
    //     $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
    //   }

    //   // Wishlist
    //   if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
    //     $this->load->model('account/wishlist');

    //     foreach ($this->session->data['wishlist'] as $key => $product_id) {
    //       $this->model_account_wishlist->addWishlist($product_id);

    //       unset($this->session->data['wishlist'][$key]);
    //     }
    //   }

      // Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
      // if (isset($this->request->post['redirect']) && $this->request->post['redirect'] != $this->url->link('account/logout', '', true) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
        // $this->response->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
      // } else {
        // $this->response->redirect($this->url->link('account/account'));
      // }
    }

    // $data['breadcrumbs'] = array();

    // $data['breadcrumbs'][] = array(
    //   'text' => $this->language->get('text_home'),
    //   'href' => $this->url->link('common/home')
    // );

    // $data['breadcrumbs'][] = array(
    //   'text' => $this->language->get('text_account'),
    //   'href' => $this->url->link('account/account', '', true)
    // );

    // $data['breadcrumbs'][] = array(
    //   'text' => $this->language->get('text_login'),
    //   'href' => $this->url->link('account/login', '', true)
    // );

    $data['error_warning'] = $this->session->data['error'] ?? '';
    $data['error_warning'] = $this->error['warning'] ?? $data['error_warning'];

    $data['action'] = $this->url->link('account/login');
    $data['register'] = $this->url->link('account/register');
    $data['forgotten'] = $this->url->link('account/forgotten');

    // // Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
    // if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
    //   $data['redirect'] = $this->request->post['redirect'];
    // } elseif (isset($this->session->data['redirect'])) {
    //   $data['redirect'] = $this->session->data['redirect'];

    //   unset($this->session->data['redirect']);
    // } else {
    //   $data['redirect'] = '';
    // }

    $data['success'] = $this->session->data['success'] ?? '';
    unset($this->session->data['success']);

    $data['email'] = $this->request->post['email'] ?? '';
    $data['password'] = $this->request->post['password'] ?? '';

    // //region Prepare Data
    // $data['mytemplate'] = $this->config->get('theme_default_directory');
    // //endregion

    // $data['column_left'] = $this->load->controller('common/column_left');
    // $data['column_right'] = $this->load->controller('common/column_right');
    // $data['content_top'] = $this->load->controller('common/content_top');
    // $data['content_bottom'] = $this->load->controller('common/content_bottom');

    $data['headingH1'] = 'Вход';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('account/login', $data));
  }

  private function validate() {
    // Check how many login attempts have been made.
    $login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

    if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
      $this->error['warning'] = $this->language->get('error_attempts');
    }

    // Check if customer has been approved.
    $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

    if ($customer_info && !$customer_info['status']) {
      $this->error['warning'] = $this->language->get('error_approved');
    }

    if (!$this->error) {
      if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
        $this->error['warning'] = $this->language->get('error_login');

        $this->model_account_customer->addLoginAttempt($this->request->post['email']);
      } else {
        $this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
      }
    }

    return !$this->error;
  }

}
