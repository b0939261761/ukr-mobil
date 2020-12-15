<?
class ControllerInformationExpectedIncome extends Controller {
  public function index() {
    $data['headingH1'] = 'Планируемые поступления';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();
    $data['isLogged'] = $this->customer->isLogged() ? true : false;
    $data['isNewsletter'] = empty($this->customer->getNewsLetter()) ? false : true;
    $data['documents'] = $this->documents();
    $data['headingDetail'] = 'Планируемое поступление на';
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
      SELECT income_number, date_expected_income,
        DATE_FORMAT(date_expected_income, '%d.%m.%Y') AS date
      FROM oc_product_expected_income
      GROUP BY date_expected_income, income_number
      ORDER BY date_expected_income DESC, income_number DESC LIMIT 10
    ";
    $documents = $this->db->query($sqlDocuments)->rows;

    foreach ($documents as &$document) {
      $sql = "
        SELECT
          pei.product_id,
          pd.name,
          COALESCE(pdc.price, p.price) AS price,
          pei.quantity,
          IF(p.image = '',
            COALESCE(
              (SELECT image FROM oc_product_image
                WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
              'placeholder.png'
            ), p.image) AS image
        FROM oc_product_expected_income pei
        INNER JOIN oc_product_description pd ON pd.product_id = pei.product_id
        INNER JOIN oc_product p ON p.product_id = pei.product_id
        LEFT JOIN oc_product_discount pdc ON pdc.product_id = pei.product_id
          AND pdc.customer_group_id = {$customerGroupId}
        WHERE pei.income_number = '{$document['income_number']}'
          AND pei.date_expected_income = '{$document['date_expected_income']}'
          AND pd.language_id = 2
        ORDER BY pd.name
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

  public function newsletter() {
    $customerId = $this->customer->getId();
    $this->db->query("UPDATE oc_customer SET newsletter = 1 WHERE customer_id = {$customerId}");
    $this->response->setOutput('');
  }
}
