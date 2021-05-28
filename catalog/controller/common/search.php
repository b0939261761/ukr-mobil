<?
class ControllerCommonSearch extends Controller {
  public function index() {
    $data['search'] = $this->request->get['search'] ?? '';
    return $this->load->view('common/search', $data);
  }

  public function getSuggestion() {
    $this->load->model('tool/image');
    $configTheme = $this->config->get('config_theme');
    $imageWidth = $this->config->get("theme_{$configTheme}_image_product_width");
    $imageHeight = $this->config->get("theme_{$configTheme}_image_product_height");

    $requestData = json_decode(file_get_contents('php://input'), true);
    $search = $requestData['search'] ?? '';
    $customerGroupId = $this->customer->getGroupId() ?? 1;

    if (strpos($this->db->escape($search), 'copy') !== false) {
      $this->response->addHeader('Content-Type: application/json');
      return $this->response->setOutput(json_encode([ 'data' => [] ]));
    }

    foreach (explode(' ', $this->db->escape($search)) as $word) {
      $implode[] = strlen($word) < 3
        ? "(pd.name LIKE '{$word}%' OR pd.name LIKE '% {$word}%')"
        : "pd.name LIKE '%{$word}%'";
    }

    $where = implode(' AND ', $implode);

    $sql = "
      SELECT
        aa.*,
        CONCAT(cp.path_id, '_', aa.category_id) AS path,
        cd.name AS categoryName0,
        IF(aa.image0 = '',
        COALESCE(
          (SELECT image FROM oc_product_image
            WHERE product_id = aa.product_id ORDER BY sort_order ASC LIMIT 1),
          'placeholder.jpg'
        ), aa.image0) AS image
      FROM (
        SELECT * FROM (
          SELECT
            cd.category_id,
            cd.name AS categoryName,
            p.product_id,
            pd.name AS productName,
            p.image AS image0,
            0.00 AS price,
            0 AS quantity,
            true AS isOwner,
            pop.price_min AS priceMin,
            pop.price_max AS priceMax
          FROM product_owner pd
            LEFT JOIN product_owner_prices pop ON pop.product_owner_id = pd.id
            LEFT JOIN oc_product p ON p.product_id = pd.product_id_default
            LEFT JOIN oc_product_to_category ptc ON ptc.product_id = p.product_id
            LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
            LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
          WHERE cp.level = 1
            AND cd.language_id = 2
            AND pop.customer_group_id = {$customerGroupId}
            AND {$where}

          UNION ALL

          SELECT
            cd.category_id,
            cd.name AS categoryName,
            pd.product_id,
            pd.name AS productName,
            p.image AS image0,
            COALESCE(pdc.price, p.price) AS price,
            (SELECT SUM(pts.quantity) FROM oc_product_to_stock pts WHERE pts.product_id = p.product_id) +
              p.quantity AS quantity,
            false AS isOwner,
            0.00 AS priceMin,
            0.00 AS priceMax
          FROM oc_product p
            LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
            LEFT JOIN oc_product_to_category ptc ON ptc.product_id = p.product_id
            LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
            LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
            LEFT JOIN oc_product_discount AS pdc ON pdc.product_id = p.product_id
              AND pdc.customer_group_id = {$customerGroupId}
          WHERE p.status = 1
            AND pd.language_id = 2
            AND cd.language_id = 2
            AND cp.level = 1 AND {$where}
          ORDER BY isOwner DESC, quantity DESC
          ) aa
        GROUP BY categoryName
        ORDER BY isOwner DESC, quantity DESC
      ) aa
      LEFT JOIN oc_category_path cp ON cp.category_id = aa.category_id
      LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
      WHERE cp.level = 0 AND cd.language_id = 2
      GROUP BY product_id
      ORDER BY isOwner DESC, quantity DESC
      LIMIT 10
    ";

    $data = $this->db->query($sql)->rows;

    foreach ($data as &$item) {
      $categoryPath = ['path' => $item['path'], 'search' => $search];
      $item['categoryUrl'] = $this->url->link('product/search', $categoryPath);
      $item['productUrl'] = $this->url->link('product/product', ['product_id' => $item['product_id']]);
      $item['productImage'] = $this->model_tool_image->resize($item['image'], $imageWidth, $imageHeight);
      if ($item['isOwner']) {
        $item['priceMin'] = $item['priceMin'] ? $this->currency->format($item['priceMin']): 0;
        $item['priceMax'] = $item['priceMax'] ? $this->currency->format($item['priceMax']): 0;
      } else {
        $item['productPrice'] = $item['price'] ? $this->currency->format($item['price']) : 0;
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode([ 'data' => $data ]));
  }
}
