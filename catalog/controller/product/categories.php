<?
class ControllerProductCategories extends Controller {
  public function index() {
    $brandId = (int)($this->request->get['brand'] ?? 0);

    $sql = "
      WITH
      tmpChildCategory AS (
        SELECT category_id FROM oc_category
        WHERE parent_id = {$this->request->request['category']}
          AND brand_id = {$brandId}
          AND status = 1
      ),
      tmpCategoriesActive AS (
        SELECT c.category_id FROM oc_category_path cp
        LEFT JOIN oc_category c ON c.category_id = cp.path_id
        WHERE cp.category_id = COALESCE((SELECT category_id FROM tmpChildCategory), {$this->request->request['category']})
          AND c.status = 1
          ORDER BY cp.level
      )
      SELECT
        IF(COUNT(name), JSON_ARRAYAGG(
          JSON_OBJECT('name', name, 'path', path, 'active', active, 'children', children)),
          JSON_ARRAY()
        ) AS value
      FROM (
        SELECT
          cd1.name,
          c1.category_id AS path,
          IF(c1.category_id = tca1.category_id, 1, 0) AS active,
          IF(COUNT(t2.name),
            JSON_ARRAYAGG(JSON_OBJECT(
              'name', t2.name, 'path', t2.path, 'active', t2.active, 'children', t2.children
            )),
            JSON_ARRAY()
          ) AS children
          FROM oc_category c1
        LEFT JOIN oc_category_description cd1 ON cd1.category_id = c1.category_id
        LEFT JOIN tmpCategoriesActive tca1 ON tca1.category_id = c1.category_id
        LEFT JOIN LATERAL (
          SELECT
            cd2.name,
            c2.parent_id,
            CONCAT(c1.category_id, '_', c2.category_id) AS path,
            IF(c2.category_id = tca2.category_id, 1, 0) AS active,
            IF(COUNT(t3.name),
              JSON_ARRAYAGG(JSON_OBJECT(
                'name', t3.name, 'path', t3.path, 'active', t3.active, 'children', t3.children
              )),
              JSON_ARRAY()
            ) AS children
          FROM oc_category c2
          LEFT JOIN oc_category_description cd2 ON cd2.category_id = c2.category_id
          LEFT JOIN tmpCategoriesActive tca2 ON tca2.category_id = c2.category_id
          LEFT JOIN LATERAL (
            SELECT
              cd3.name,
              c3.parent_id,
              CONCAT(c1.category_id, '_', c2.category_id, '_', c3.category_id) AS path,
              IF(c3.category_id = tca3.category_id, 1, 0) AS active,
              JSON_ARRAY() AS children
            FROM oc_category c3
            LEFT JOIN oc_category_description cd3 ON cd3.category_id = c3.category_id
            LEFT JOIN tmpCategoriesActive tca3 ON tca3.category_id = c3.category_id
            WHERE c3.parent_id = tca2.category_id AND c3.status = 1
            ORDER BY c3.sort_order, cd3.name
          ) AS t3 ON c2.category_id = t3.parent_id
          WHERE c2.parent_id = tca1.category_id AND c2.status = 1
          GROUP BY c2.category_id
          ORDER BY c2.sort_order, cd2.name
        ) AS t2 ON c1.category_id = t2.parent_id
        WHERE c1.parent_id = 0 AND c1.status = 1
        GROUP BY c1.category_id
        ORDER BY c1.sort_order, cd1.name
      ) AS t1
    ";

    $categories = json_decode($this->db->query($sql)->row['value'], true);
    $data['categories'] = $this->loopCategory($categories);
    return $this->load->view('product/categories', $data);
  }

  private function loopCategory ($categories) {
    foreach ($categories as &$category) {
      $category['link'] = $this->url->link('product/category', ['path' => $category['path']]);
      $children = $category['children'];
      if (count($children)) {
        $category['children'] = $this->loopCategory($children);
      }
    }
    return $categories;
  }
}
