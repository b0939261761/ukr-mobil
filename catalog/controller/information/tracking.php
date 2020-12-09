<?
class ControllerInformationTracking extends Controller {
  public function index() {
    $data['headingH1'] = 'Проверка статуса заказа';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('information/tracking', $data));
  }

  public function getStatus() {
    $requestData = json_decode(file_get_contents('php://input'), true);

    if (!empty($requestData['orderId']) && !empty($requestData['phone'])) {
      $sql = "
        SELECT name, price, state, MAX(datetime) AS datetime
        FROM sc_order_state_list
        WHERE id = '{$requestData['orderId']}' AND phone = '{$requestData['phone']}'
        GROUP BY state
        ORDER BY datetime
      ";

      $list = $this->db->query($sql)->rows;
    }

    $this->response->setOutput(json_encode($list ?? []));
  }
}
