<?
namespace Ego\Providers;

class ProductFilterProvider {
  private $registry;

  private $customerGroupId = 1;
  private $customerId = 0;
  private $productLimit;
  private $imageWidth;
  private $imageHeight;

  private $route = '';
  private $path = '';
  private $search = '';
  private $searchWords = [];
  private $category = 0;
  private $brand = 0;
  private $model = 0;
  private $available = 0;
  private $stock = 0;
  private $page = 1;
  private $filters = [];

  public function __get($key) {
    return $this->registry->get($key);
  }

  public function __set($key, $value) {
    $this->registry->set($key, $value);
  }

  function __construct($registry) {
    $this->registry = $registry;
  }

  private function prepareWhereProduct() {
    $sql = empty($this->category)
    ? " FROM oc_product p"
    : " FROM oc_category_path cp
      LEFT JOIN oc_product_to_category p2c ON cp.category_id = p2c.category_id
      LEFT JOIN oc_product p ON p2c.product_id = p.product_id";

    if (!empty($this->brand) || !empty($this->model)) {
      $sql .= " LEFT JOIN products_models pm ON pm.product_id = p.product_id";
      if (empty($this->model)) $sql .= " LEFT JOIN models m ON m.id = pm.model_id ";
    }

    if (!empty($this->filters)) {
      $filters = [];
      foreach ($this->filters as $value) $filters[] = "filter_value_id = {$value}";
      $filtersSQL = implode(' OR ', $filters);
      $countFilter = count($this->filters);

      $sql .= "
        INNER JOIN LATERAL (
          SELECT product_id FROM products_filters
          WHERE product_id = p.product_id AND {$filtersSQL}
          GROUP BY product_id
          HAVING COUNT(*) = {$countFilter}
        ) pf ON pf.product_id = p.product_id
      ";
    }

    $sql .= "
      LEFT JOIN oc_product_description pd ON p.product_id = pd.product_id
      WHERE pd.language_id = 2
        AND p.status = 1
    ";

    if (!empty($this->category)) $sql .= " AND cp.path_id = {$this->category}";

    if (!empty($this->searchWords)) {
      $words = [];
      foreach ($this->searchWords as $word) {
        $words[] = strlen($word) < 3
        ? "(pd.name LIKE '{$word}%' OR pd.name LIKE '% {$word}%')"
        : "pd.name LIKE '%{$word}%'";
      }

      $wordsSQL = implode(' AND ', $words);
      if ($wordsSQL) $sql .= " AND (({$wordsSQL}) OR p.product_id = '{$this->searchWords[0]}')";
    }

    if (!empty($this->model)) $sql .= " AND pm.model_id = {$this->model}";
    elseif (!empty($this->brand)) $sql .= " AND m.brand_id = {$this->brand}";

    if (!empty($this->stock)) {
      if ($this->stock == 1) $sql .= " AND p.quantity > 0";
      if ($this->stock == 2) $sql .= " AND p.quantity_store_2 > 0";
    } else if (!empty($this->available)) $sql .= " AND p.quantity + p.quantity_store_2 > 0";

    $sql .= " GROUP BY p.product_id";
    return $sql;
  }

  // ----------------------------

  private function prepareWhereOwner() {
    $sql = empty($this->category)
    ? " FROM product_owner po
        LEFT JOIN oc_product p ON p.product_id = po.product_id_default"
    : " FROM oc_category_path cp
        LEFT JOIN oc_product_to_category p2c ON cp.category_id = p2c.category_id
        LEFT JOIN oc_product p ON p2c.product_id = p.product_id
        INNER JOIN product_owner po ON po.product_id_default = p.product_id";

    if (!empty($this->brand) || !empty($this->model)) {
      $sql .= " LEFT JOIN products_models pm ON pm.product_id = p.product_id";
      if (empty($this->model)) $sql .= " LEFT JOIN models m ON m.id = pm.model_id ";
    }

    if (!empty($this->filters)) {
      $filters = [];
      foreach ($this->filters as $value) $filters[] = "filter_value_id = {$value}";
      $filtersSQL = implode(' OR ', $filters);
      $countFilter = count($this->filters);

      $sql .= "
        INNER JOIN LATERAL (
          SELECT product_id FROM products_filters
          WHERE product_id = p.product_id AND {$filtersSQL}
          GROUP BY product_id
          HAVING COUNT(*) = {$countFilter}
        ) pf ON pf.product_id = p.product_id
      ";
    }

    $sql .= "
      LEFT JOIN oc_product_description pd ON p.product_id = pd.product_id
      LEFT JOIN product_owner_prices pop ON pop.product_owner_id = po.id
            AND pop.customer_group_id = {$this->customerGroupId}
      WHERE pd.language_id = 2
        AND p.status = 1
    ";

    if (!empty($this->category)) $sql .= " AND cp.path_id = {$this->category}";

    if (!empty($this->searchWords)) {
      $words = [];
      foreach ($this->searchWords as $word) {
        $words[] = strlen($word) < 3
        ? "(po.name LIKE '{$word}%' OR po.name LIKE '% {$word}%')"
        : "po.name LIKE '%{$word}%'";
      }

      $wordsSQL = implode(' AND ', $words);
      if ($wordsSQL) $sql .= " AND {$wordsSQL}";
    }

    if (!empty($this->model)) $sql .= " AND pm.model_id = {$this->model}";
    elseif (!empty($this->brand)) $sql .= " AND m.brand_id = {$this->brand}";

    if (!empty($this->stock) || !empty($this->available)) {
      $sql .= " AND po.quantity > 0";
    }

    $sql .= " GROUP BY p.product_id";
    return $sql;
  }


  // ----------------------------

  private function getProductProperties(int $productId) {
    $sql = "
      SELECT cc.id, cc.name
      FROM products_properties aa
      LEFT JOIN product_property_values bb ON bb.id = aa.product_property_value_id
      LEFT JOIN product_properties cc ON cc.id = bb.product_property_id
      WHERE aa.product_id = {$productId}
      GROUP BY bb.product_property_id
      ORDER BY cc.ord
    ";
    return $this->db->query($sql)->rows;
  }

  private function getProductPropertyValues(int $productId, int $productPropertyId) {
    $sql = "
      SELECT bb.name, bb.color
      FROM products_properties aa
      LEFT JOIN product_property_values bb ON bb.id = aa.product_property_value_id
      WHERE aa.product_id = {$productId} AND bb.product_property_id = {$productPropertyId}
      ORDER BY bb.ord
    ";
    return $this->db->query($sql)->rows;
  }

  private function getProductsProperties(int $productId) {
    $productProperties = [];

    foreach ($this->getProductProperties($productId) as $property) {
      $values = [];

      foreach ($this->getProductPropertyValues($productId, $property['id'] ) as $value) {
        $values[] = [
          'name' => $value['name'],
          'color' => $value['color']
        ];
      }

      $productProperties[] = [
        'name' => $property['name'],
        'values' => $values
      ];
    }

    return $productProperties;
  }

  // --------------------------

  private function filterItems() {
    $this->load->model('tool/image');

    $start = ($this->page - 1) * $this->productLimit;

    $sql = "
      SELECT
        *,
        (SELECT price
          FROM oc_product_special
          WHERE product_id = a.product_id
            AND customer_group_id = {$this->customerGroupId}
            AND (date_start = '0000-00-00' OR date_start < NOW())
            AND (date_end = '0000-00-00' OR date_end > NOW())
          ORDER BY priority ASC, price ASC LIMIT 1) AS special,
        IF(image = '',
          COALESCE(
            (SELECT image FROM oc_product_image
              WHERE product_id = a.product_id ORDER BY sort_order LIMIT 1),
            'placeholder.jpg'
          ), image) AS image,
        (SELECT COUNT(1) AS cnt FROM oc_customer_wishlist
          WHERE customer_id = {$this->customerId} AND product_id = a.product_id) > 0 AS is_wishlist,
        quantityStore1 + quantityStore2 AS quantity
      FROM (
        SELECT
            p.product_id,
            po.name,
            p.image,
            p.price,
            p.minimum,
            po.quantity AS quantityStore1,
            0 AS quantityStore2,
            1 as is_owner,
            pop.price_min,
            pop.price_max,
            p.sort_order
          {$this->prepareWhereOwner()}

        UNION ALL

        SELECT
            p.product_id,
            pd.name,
            p.image,
            COALESCE((SELECT price FROM oc_product_discount
                WHERE product_id = p.product_id AND customer_group_id = {$this->customerGroupId}
                ORDER BY priority ASC, price ASC LIMIT 1),
              p.price) AS price,
            p.minimum,
            p.quantity AS quantityStore1,
            p.quantity_store_2,
            0 AS is_owner,
            price AS price_min,
            price AS price_max,
            p.sort_order
          {$this->prepareWhereProduct()}

        ORDER BY is_owner DESC, sort_order ASC, name
      ) a
      ORDER BY is_owner DESC, sort_order ASC, name
      LIMIT {$start},{$this->productLimit}
    ";

    // file_put_contents('./ego/src/Providers/___LOG___.txt', $sql);

    $items = $this->db->query($sql)->rows;

    foreach ($items as &$item) {
      $item['href'] = $this->url->link('product', "product_id={$item['product_id']}");
      if ($item['minimum'] < 1) $item['minimum'] = 1;
      $item['image'] = $this->model_tool_image->resize($item['image'], $this->imageWidth, $this->imageHeight);
      $item['price'] = $item['price'] ? $this->currency->format($item['price']) : 0;
      $item['special'] = $item['special'] ? $this->currency->format($item['special']): 0;
      $item['price_min'] = $item['price_min'] ? $this->currency->format($item['price_min']): 0;
      $item['price_max'] = $item['price_max'] ? $this->currency->format($item['price_max']): 0;
      $item['productProperties'] = $this->getProductsProperties($item['product_id']);
    }

    return $items;
  }

  private function filterTotal() {
    $sql = "
      SELECT COUNT(product_id) AS total FROM (
        SELECT p.product_id {$this->prepareWhereOwner()}
        UNION ALL
        SELECT p.product_id {$this->prepareWhereProduct()}
      ) a
    ";
    return $this->db->query($sql)->row['total'];
  }

  private function getPagination() {
    $query = ['page' => "{page}"];
    if (!empty($this->path)) $query['path'] = $this->path;
    if (!empty($this->search)) $query['search'] = $this->search;
    if (!empty($this->brand)) $query['brand'] = $this->brand;
    if (!empty($this->model)) $query['model'] = $this->model;
    if (!empty($this->stock)) $query['stock'] = $this->stock;
    if (!empty($this->available)) $query['available'] = $this->available;

    foreach ($this->filters as $key => $value) {
      if (!empty($value)) $query[$key] = $value;
    }

    $total = $this->filterTotal();
    return [
      'isNotLastPage' => $this->page != ceil($total / $this->productLimit),
      'total' => $total,
      'page' => $this->page,
      'limit' => $this->productLimit,
      'url' => $this->url->link($this->route, $query)
    ];
  }

  public function filter(array $data = []) {
    $this->path = $data['path'];
    unset($data['path']);
    $this->search = $data['search'];
    unset($data['search']);
    $this->category = $data['category'];
    unset($data['category']);
    $this->brand = (int)($data['brand'] ?? 0);
    unset($data['brand']);
    $this->model = (int)($data['model'] ?? 0);
    unset($data['model']);
    $this->stock = (int)($data['stock'] ?? 0);
    unset($data['stock']);
    $this->available = $data['available'];
    unset($data['available']);
    $this->page = $data['page'];
    unset($data['page']);
    $this->route = $data['route'];
    unset($data['route']);
    $this->filters = $data;

    $this->customerGroupId = $this->customer->getGroupId() ?? 1;
    $this->customerId = $this->customer->getId() ?? 0;

    $configTheme = $this->config->get('config_theme');
    $this->productLimit = $this->config->get("theme_{$configTheme}_product_limit");
    $this->imageWidth = $this->config->get("theme_{$configTheme}_image_product_width");
    $this->imageHeight = $this->config->get("theme_{$configTheme}_image_product_height");

    if (!empty($this->search)) $this->searchWords = explode(' ', $this->db->escape($this->search));

    return [
      'items' => $this->filterItems(),
      'pagination' => $this->getPagination()
    ];
  }
}
