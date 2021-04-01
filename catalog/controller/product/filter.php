<?
class ControllerProductFilter extends Controller {
  public function index() {
    $data['available'] = $this->request->get['available'] ?? null;
    $data['filters'] = $this->getFilters();
    return $this->load->view('product/filter', $data);
  }

  private function getFilters() {
    $category = $this->request->request['category'];
    $search = $this->request->get['search'] ?? '';

    $sql = "
      SELECT
        f.name,
        f.queryKey,
        JSON_ARRAYAGG(JSON_OBJECT('id', fv.id, 'name', fv.name, 'ord', fv.ord)) AS `values`
      FROM categories_filters cf
      LEFT JOIN filters f ON f.id = cf.filter_id
      LEFT JOIN filter_values fv ON fv.filter_id = f.id
      WHERE cf.category_id = {$category}
      GROUP by cf.filter_id
      ORDER BY f.ord
    ";

    $filters = $this->db->query($sql)->rows;

    $brands = [
      'name' => 'Бренд',
      'queryKey' => 'brand',
      'values' => $this->getBrands($category, $search)
    ];

    $models = [
      'name' => 'Модель',
      'queryKey' => 'model',
      'values' => $this->getModels((int)($this->request->get['brand'] ?? 0), $category, $search)
    ];

    // TODO use db.store
    $stocks = [
      'name' => 'Склад',
      'queryKey' => 'stock',
      'values' =>[
        ['id' => 1, 'name' => 'г. Черновцы'],
        ['id' => 2, 'name' => 'г. Ровно']
      ]
    ];

    array_unshift($filters, $brands, $models);
    $filters[] = $stocks;

    foreach ($filters as &$item) {
      $queryKey = $item['queryKey'];
      $paramId = (int)($this->request->get[$queryKey] ?? 0);
      $itemValues = $item['values'];
      $values = gettype($itemValues) == 'array' ? $itemValues : json_decode($itemValues, true);

      foreach ($values as &$value) {
        if ($value['id'] == $paramId) $value['selected'] = 'selected';

        $query = [$queryKey => $value['id']];
        if (isset($this->request->get['path'])) $query['path'] = $this->request->get['path'];

        $requestFilters = $this->request->request['filters'];
        if (count($requestFilters) == 1) {
          $firstFilterValue = $requestFilters[0]['value'];
          $firstFilterKey = $requestFilters[0]['key'];
          if ($firstFilterKey != $queryKey) $query[$firstFilterKey] = $firstFilterValue;
        }

        $value['link'] = $this->url->link($this->request->get['route'], $query);
      }
      $item['values'] = $values;
    }
    return $filters;
  }

  private function getSQLWhereSearch($search) {
    if (empty($search)) return '';
    $implodeProduct = [];
    $implodeOwner = [];

    foreach (explode(' ', $this->db->escape($search)) as $word) {
      $implodeProduct[] = strlen($word) < 3
      ? "(pd.name LIKE '{$word}%' OR pd.name LIKE '% {$word}%')"
      : "pd.name LIKE '%{$word}%'";

      $implodeOwner[] = strlen($word) < 3
        ? "(po.name LIKE '{$word}%' OR po.name LIKE '% {$word}%')"
        : "po.name LIKE '%{$word}%'";
    }

    $sqlImplodeProduct = implode(' AND ', $implodeProduct);
    $sqlImplodeOwner = implode(' AND ', $implodeOwner);
    return " AND (({$sqlImplodeProduct}) OR p.sku = '{$search}' OR ({$sqlImplodeOwner}))";
  }

  private function getBrands($category, $search) {
    $sql = "SELECT DISTINCT b.id, b.name";

    $sql .= $category
      ? " FROM oc_category_path cp
        LEFT JOIN oc_product_to_category ptc ON ptc.category_id = cp.category_id
        LEFT JOIN oc_product p ON p.product_id = ptc.product_id"
      : " FROM oc_product p";

    if ($search) {
      $sql .= "
        LEFT JOIN oc_product_description pd ON p.product_id = pd.product_id
        LEFT JOIN product_owner po ON p.product_id = po.product_id_default
      ";
    }

    $sql .= "
    LEFT JOIN products_models pm ON pm.product_id = p.product_id
    LEFT JOIN models m ON m.id = pm.model_id
    LEFT JOIN brands b ON b.id = m.brand_id
      WHERE b.id IS NOT NULL
    ";

    if ($category) $sql .= " AND cp.path_id = {$category}";

    $sql .= "{$this->getSQLWhereSearch($search)} ORDER BY b.ord, b.name";
    return $this->db->query($sql)->rows;
  }

  private function getModels($brand, $category, $search) {
    if (empty($brand)) return [];
    $sql = "SELECT DISTINCT m.id, m.name";

    $sql .= $category
      ? " FROM oc_category_path cp
        LEFT JOIN oc_product_to_category ptc ON ptc.category_id = cp.category_id
        LEFT JOIN oc_product p ON p.product_id = ptc.product_id"
      : " FROM oc_product p";

    if ($search) {
      $sql .= "
        LEFT JOIN oc_product_description pd ON p.product_id = pd.product_id
        LEFT JOIN product_owner po ON p.product_id = po.product_id_default
      ";
    }

    $sql .= "
      LEFT JOIN products_models pm ON pm.product_id = p.product_id
      LEFT JOIN models m ON m.id = pm.model_id
      WHERE m.id IS NOT NULL AND m.brand_id = {$brand}
    ";

    if ($category) $sql .= " AND cp.path_id = {$category}";
    $sql .= "{$this->getSQLWhereSearch($search)} ORDER BY m.ord, m.name";
    return $this->db->query($sql)->rows;
  }

  public function models() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $category = (int)$requestData['category'] ?? 0;
    $brand = (int)$requestData['brand'] ?? 0;
    $search = $requestData['search'] ?? '';
    if (!$brand) return $this->response->setOutput(json_encode(['code' => 404]));
    $this->response->setOutput(json_encode($this->getModels($brand, $category, $search)));
  }
}
