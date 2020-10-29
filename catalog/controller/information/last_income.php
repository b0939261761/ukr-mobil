<?
class ControllerInformationLastIncome extends Controller {
  public function index() {
    $data['headingH1'] = 'Последние поступления';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $data['isLogged'] = $this->customer->isLogged() ? true : false;
    $data['isNewsletter'] = empty($this->customer->getNewsLetter()) ? false : true;
    $data['documents'] = $this->documents();
    $data['headingDetail'] = 'Поступление от';
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('information/income', $data));
  }

  private function documents() {
    $this->load->model('tool/image');
    $configTheme = $this->config->get('config_theme');
    $imageWidth = $this->config->get("theme_{$configTheme}_image_product_width");
    $imageHeight = $this->config->get("theme_{$configTheme}_image_product_height");
    $customerGroupId = $this->customer->getGroupId() ?? 0;

    $sqlDocuments = "
      SELECT income_number, date_income,
        DATE_FORMAT(date_income, '%d.%m.%Y') AS date
      FROM oc_product_last_income
      GROUP BY date_income, income_number
      ORDER BY date_income DESC, income_number DESC LIMIT 10
    ";
    $documents = $this->db->query($sqlDocuments)->rows;

    foreach ($documents as &$document) {
      $sql = "
        SELECT aa.product_id, bb.name, COALESCE(dd.price, cc.price) AS price,
          aa.quantity, cc.image
        FROM oc_product_last_income aa
        INNER JOIN oc_product_description bb ON bb.product_id = aa.product_id
        INNER JOIN oc_product cc ON cc.product_id = aa.product_id
        LEFT JOIN oc_product_discount dd ON dd.product_id = aa.product_id
          AND dd.customer_group_id = {$customerGroupId}
        WHERE aa.income_number = '{$document['income_number']}'
          AND aa.date_income = '{$document['date_income']}'
          AND bb.language_id = 2
        ORDER BY bb.name
      ";

      foreach ($this->db->query($sql)->rows as $product) {
        $document['products'][] = [
          'image'    => $this->model_tool_image->resize($product['image'], $imageWidth, $imageHeight),
          'code'     => "Код: {$product['product_id']}",
          'name'     => $product['name'],
          'price'    => "$" . round($product['price'], 2),
          'quantity' => "{$product['quantity']} шт.",
          'href'     => $this->url->link('product/product', ['product_id' => $product['product_id']])
        ];
      }
    }
    return $documents;
  }
}
