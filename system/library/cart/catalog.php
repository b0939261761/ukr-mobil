<?
namespace Cart;
class Catalog {
  private $currentCategories;
  private $categoryId;
  private $search;
  private $searchSQL;

  // private $brand;
  // private $model;
  // private $stock;
  private $path;
  private $page;
  private $sort;

  private $items;
  private $itemsTotal;
  private $itemsPerPage = 10;

  private $customerGroupId = 1;


  private $filterList;

  public function __construct($registry) {
    $this->db = $registry->get('db');
    $this->url = $registry->get('url');
    $this->image = $registry->get('image');
  }

  public function setCategoryId($categoryId) {
    $this->categoryId = $categoryId;
  }

  public function getCategoryId() {
    return $this->categoryId;
  }

  public function setPath($path) {
    $this->path = $path;
  }

  public function getPath() {
    return $this->path;
  }

  public function setSort($sort) {
    $this->sort = $sort;
  }

  public function getSort() {
    return $this->sort;
  }

  public function setSearch($search) {
    $search = preg_replace('/\s+/', ' ', str_replace('\\', '', trim($search)));
    if (!$search) return;
    $this->search = $search;

    foreach (explode(' ', $search) as $word) {
      $implode[] = strlen($word) < 3
        ? "(pd.name LIKE '{$word}%' OR pd.name LIKE '% {$word}%')"
        : "pd.name LIKE '%{$word}%'";
    }

    $this->searchSQL = implode(' AND ', $implode);
  }

  public function getSearch() {
    return $this->search;
  }

  public function getSearchSQL() {
    return $this->searchSQL;
  }

  public function setPage($page) {
    $this->page = $page;
  }

  public function getPage() {
    return $this->page;
  }

  public function getItemsPerPage() {
    return $this->itemsPerPage;
  }

  public function getCurrentCatagories() {
    if ($this->currentCategories) return $this->currentCategories;

    $sql = "
      SELECT c.category_id AS id, cd.name FROM oc_category_path cp
      LEFT JOIN oc_category c ON c.category_id = cp.path_id
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE cp.category_id = {$this->db->escape($this->categoryId)} AND c.status = 1
      ORDER BY level
    ";
    $this->currentCategories = $this->db->query($sql)->rows;
    return $this->currentCategories;
  }


  public function getFilters($keywords = null) {
    if (isset($this->filterList)) return $this->filterList;

    $keywordList = [];
    foreach ($keywords as $keyword) $keywordList[] = "'{$this->db->escape($keyword)}'";
    $sqlKeywords = implode(',', $keywordList);

    $this->filterList = [];
    if ($sqlKeywords) {
      $sql = "
        SELECT id, queryKey AS `key`, queryValue AS value, keyword, name
        FROM seo_filter_url WHERE keyword IN ({$sqlKeywords}) ORDER BY ord
      ";
      $this->filterList = $this->db->query($sql)->rows;
    }

    return $this->filterList;
  }


  public function getItems() {
    if (isset($this->items)) return $this->items;

    $sql = "
      WITH
      tmpProducts AS (
        SELECT
          p.product_id AS id,
          pd.name,
          p.isLatest,
          p.isSalesLeader,
          IF(p.dateExpected = '0000-00-00', '', DATE_FORMAT(p.dateExpected, '%d.%m.%Y')) AS dateExpected,
          IF(p.image = '',
            COALESCE(
              (SELECT image FROM oc_product_image
                WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
              'placeholder.jpg'
            ),
            p.image) AS image,
          COALESCE(
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = {$this->customerGroupId}),
            p.price) AS priceOld,
          p.quantity AS quantityStore1,
          p.quantity_store_2 AS quantityStore2,
          pgp.priceMin,
          pgp.priceMax,
          IF(COUNT(prop.name),
            JSON_ARRAYAGG(JSON_OBJECT(
              'ord', prop.ord, 'name', prop.name, 'values', prop.`values`, 'isColor', isColor
            )),
            JSON_ARRAY()
          ) AS properties
        FROM oc_category_path cp
        LEFT JOIN oc_product_to_category p2c ON cp.category_id = p2c.category_id
        LEFT JOIN oc_product p ON p2c.product_id = p.product_id
        LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
        LEFT JOIN product_group_prices pgp ON pgp.product_group_id = p.product_group_id
          AND customer_group_id = {$this->customerGroupId}
        LEFT JOIN LATERAL (
          SELECT
            ppr.name, ppr.ord, ppr.name = 'Цвет' AS isColor,
            JSON_ARRAYAGG(JSON_OBJECT(
              'ord', ppv.ord, 'name', ppv.name, 'color', ppv.color,
              'isActive', prpr.product_id_link = p.product_id,
              'id', prpr.product_id_link
              )) AS `values`
          FROM products_properties prpr
          LEFT JOIN product_property_values ppv ON ppv.id = prpr.product_property_value_id
          LEFT JOIN product_properties ppr ON ppr.id = ppv.product_property_id
          WHERE prpr.product_id = p.product_id
          GROUP BY ppr.id
        ) prop ON true
        WHERE p.status = 1 AND cp.path_id = {$this->categoryId}
        GROUP BY p.product_id
        ORDER BY p.date_added DESC, p.product_id DESC
        LIMIT 10
      ),
      tmpProductsFull AS (
        SELECT
        *,
        COALESCE(
          (SELECT price
            FROM oc_product_special
            WHERE product_id = p.id
              AND customer_group_id = {$this->customerGroupId}
              AND (date_start = '0000-00-00' OR date_start < NOW())
              AND (date_end = '0000-00-00' OR date_end > NOW())
          ),
          priceOld) AS price
        FROM tmpProducts p
      )
      SELECT
        p.id,
        p.name,
        p.image,
        p.isLatest,
        p.isSalesLeader,
        p.dateExpected,
        p.price = p.priceOld AS isPromotions,
        p.quantityStore1,
        p.quantityStore2,
        p.quantityStore1 + p.quantityStore2 AS quantity,
        p.price AS priceUSD,
        ROUND(p.price * c.value) AS priceUAH,
        ROUND(p.priceOld * c.value) AS priceOldUAH,
        p.priceMin AS priceMinUSD,
        p.priceMax AS priceMaxUSD,
        ROUND(p.priceMin * c.value) AS priceMinUAH,
        ROUND(p.priceMax * c.value) AS priceMaxUAH,
        p.properties
      FROM tmpProductsFull p
      LEFT JOIN oc_currency c ON c.currency_id = 980
    ";


    // file_put_contents('./system/library/cart/___LOG___.txt', $sql);

    $this->items = $this->db->query($sql)->rows;

    foreach ($this->items as &$item) {
      $item['link'] = $this->url->link('product', ['product_id' => $item['id']]);
      $item['image'] = $this->image->resize($item['image'], 306, 306);

      $item['properties'] = json_decode($item['properties'], true);
      uasort($item['properties'], function ($a, $b) { return $a['ord'] - $b['ord']; });

      foreach ($item['properties'] as &$property) {
        uasort($property['values'], function ($a, $b) { return $a['ord'] - $b['ord']; });

        foreach ($property['values'] as &$value) {
          if (!$value['isActive']) $value['link'] = $this->url->link('product', ['product_id' => $value['id']]);
        }
      }
    }

    return $this->items;
  }


  public function getItemsTotal() {
    if (isset($this->itemsTotal)) return $this->itemsTotal;

    // $sql = "
    //   SELECT COUNT(product_id) AS total FROM (
    //     SELECT p.product_id {$this->prepareWhereOwner()}
    //     UNION ALL
    //     SELECT p.product_id {$this->prepareWhereProduct()}
    //   ) a
    // ";
    // return $this->db->query($sql)->row['total'];

    $this->itemsTotal = 69;
    return $this->itemsTotal;
  }

}
