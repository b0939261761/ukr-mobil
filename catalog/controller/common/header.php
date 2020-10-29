<?php
// use Ego\Controllers\BaseController;
class ControllerCommonHeader extends Controller {
  public function index() {
    $server = $this->config->get('config_' . $this->request->server['HTTPS'] ? 'ssl' : 'url');

    // if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
    //   $this->document->addLink("{$server}image/{$this->config->get('config_icon')}", 'icon');
    // }

    $uri = substr(explode('?', $this->request->server['REQUEST_URI'], 2)[0], 1);
    $this->document->addLink("{$server}{$uri}", 'canonical');

    $data['title'] = $this->document->getTitle();
    $data['base'] = $server;
    $data['description'] = $this->document->getDescription();
    $data['keywords'] = $this->document->getKeywords();
    $data['metaList'] = $this->document->getMetaList();
    $data['microdata'] = $this->document->getMicrodata();
    $data['dataLayer'] = $this->document->getDataLayer();
    $data['links'] = $this->document->getLinks();
    $data['styles'] = $this->document->getStyles();
    $data['scripts'] = $this->document->getScripts('header');
    $data['name'] = $this->config->get('config_name');
    $data['logo'] = "{$server}/image/{$this->config->get('config_logo')}";

    // $this->load->language('common/header');

    // $data['customerName'] = '';
    // $data['balance'] = '';
    $data['isLogged'] = $this->customer->isLogged();

    if ($data['isLogged']) {
      $data['customerName'] = "{$this->customer->getFirstName()} {$this->customer->getLastName()}";
      $sql = "SELECT balance FROM oc_customer WHERE customer_id = {$this->customer->getId()}";
      $data['balance'] = $this->db->query($sql)->row['balance'];
    }

    $data['home'] = $this->url->link('common/home');
    $data['login'] = $this->url->link('account/login');
    $data['logout'] = $this->url->link('account/logout');
    $data['accountDocuments'] = "{$this->url->link('account/account')}#documents";
    $data['accountOrders'] = "{$this->url->link('account/account')}#orders";
    $data['accountProfile'] = "{$this->url->link('account/account')}#profile";
    $data['accountTerms'] = "{$this->url->link('account/account')}#terms";

    // $data['language'] = $this->load->controller('common/language');
    // $data['currency'] = $this->load->controller('common/currency');
    $data['search'] = $this->load->controller('common/search');
    $data['cart'] = $this->load->controller('common/cart');
    $data['headerMenu'] = $this->load->controller('common/header_menu');
    $data['headerCategory'] = $this->load->controller('common/header_category');

    // $type = "module";
    // $this->load->model('setting/module');
    // $result = $this->model_setting_module->getModule($type);
    // foreach ($result as $result) {
    //   if ($result['code'] === "blogger") {
    //     $data['blog_enable'] = 1;
    //   }
    // }

    $usd = (new \Ego\Models\Currency())->get('UAH', true);
    $data['rate'] = number_format($usd->getValue() ?? 0, 2);
    return $this->load->view('common/header', $data);
  }
}
