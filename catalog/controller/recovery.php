<?
class ControllerRecovery extends Controller {
  private $errorPassword = '';
  private $errorConfirm = '';

  public function index() {
    $data['headingH1'] = 'Відновлення паролю';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();

    $this->document->addCustomStyle('/resourse/styles/recovery.min.css');
    $this->document->addPreload('/resourse/scripts/recovery.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/recovery.min.js');


    $code = $this->db->escape($this->request->get['code'] ?? '');
    $sql = "SELECT customer_id AS id, email FROM oc_customer WHERE code = '{$code}'";
    $customer = $this->db->query($sql)->row;

    if (empty($customer)) {
      $data['hasInvalidCode'] = empty($customer);
      $this->customer->reset();
    } else {
      $this->session->data['customerId'] = $customer['id'];
    }

    $breacrumbsData = ['breadcrumbs' => [['name' => $data['headingH1']]]];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);
    $data['rightMenu'] = $this->load->controller('shared/components/right_menu');
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('recovery/recovery', $data);
  }

  public function recovery() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $password = $this->db->escape(trim($requestData['password'] ?? ''));
    $customerId = $this->session->data['customerId'];
    $length = utf8_strlen($password);

    if (empty($customerId) || $length < 4 || $length > 20) {
      http_response_code(400);
      exit();
    }

    $salt = token(9);
    $sql = "
      UPDATE oc_customer SET
        password = SHA1(CONCAT('{$salt}', SHA1(CONCAT('{$salt}', SHA1('{$password}'))))),
        salt     = '{$salt}',
        code     = ''
      WHERE customer_id = {$customerId}
    ";

    $this->db->query($sql);
    echo $this->url->link('account');
  }
}
