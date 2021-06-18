<?
class ControllerTracking extends Controller {
  public function index() {
    $data['headingH1'] = 'Сервіс';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();

    $this->document->addCustomStyle('/resourse/styles/tracking.min.css');
    $this->document->addPreload('/resourse/scripts/tracking.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/tracking.min.js');

    $breacrumbsData = ['breadcrumbs' => [['name' => $data['headingH1']]]];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);

    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('tracking/tracking', $data);
  }

  public function getStatus() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $orderId = $this->db->escape($requestData['orderId'] ?? '');
    $phone = $this->db->escape($requestData['phone'] ?? '');

    if ($orderId && $phone) {
      $sql = "
        SELECT
        COALESCE(name, '') AS name,
        COALESCE(MAX(price), 0) AS price,
        IF(COUNT(name),
          JSON_ARRAYAGG(JSON_OBJECT('name', state, 'datetime', `datetime`, 'dateFormat', dateFormat)),
          JSON_ARRAY()) AS states
        FROM (
          SELECT name, price, state AS state, datetime,
            DATE_FORMAT(MAX(datetime), '%H:%i %d.%m.%Y') AS dateFormat
          FROM sc_order_state_list
          WHERE id = '{$orderId}' AND phone = '{$phone}'
          GROUP BY state
        ) t
      ";

      $order = $this->db->query($sql)->row;
      $name = $order['name'];
      $price = $order['price'];
      $states = json_decode($order['states'], true);
      uasort($states, function ($a, $b) { return strnatcmp($a['datetime'], $b['datetime']); });
    }

    echo json_encode([
      'name'   => $name ?? '',
      'price'  => $price ?? 0,
      'states' => $states ?? []
    ]);
  }
}
