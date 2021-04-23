<?
class ControllerCommonHeaderCategory extends Controller {
  public function index() {
    $data['categories'] = [];

    foreach ($this->getCategoryList() as $child0) {
      $childrenData0 = [];

      foreach ($child0['children'] as $child1) {
        $childrenData1 = [];

        foreach ($child1['children'] as $child2) {
          $childrenData1[] = [
            'name' => $child2['name'],
            'link' => $this->url->link('product/category', ['path' => $child2['path']]),
          ];
        }

        $childrenData0[] = [
          'name' => $child1['name'],
          'link' => $this->url->link('product/category', ['path' => $child1['path']]),
          'children' => $childrenData1
        ];
      }

      $data['categories'][] = [
        'name' => $child0['name'],
        'link' => $this->url->link('product/category', ['path' => $child0['path']]),
        'children' => $childrenData0
      ];
    }

    return $this->load->view('common/header_category', $data);
  }


  private function getCategoryList() {
    $sql = "
      SELECT
        IF(count(path), JSON_ARRAYAGG(JSON_OBJECT('path', path, 'name', name, 'children', children)), JSON_ARRAY()) AS value
      FROM (
        SELECT
          c1.category_id AS path,
          cd1.name,
          IF(count(t2.path), JSON_ARRAYAGG(JSON_OBJECT('path', t2.path, 'name', t2.name, 'children', t2.children)), JSON_ARRAY()) AS children
          FROM oc_category c1
        LEFT JOIN oc_category_description cd1 ON cd1.category_id = c1.category_id
        LEFT JOIN LATERAL (
          SELECT
            CONCAT(c1.category_id, '_', c2.category_id) AS path,
            cd2.name,
            IF(count(t3.path), JSON_ARRAYAGG(JSON_OBJECT('path', t3.path, 'name', t3.name)), JSON_ARRAY()) AS children
          FROM oc_category c2
          LEFT JOIN oc_category_description cd2 ON cd2.category_id = c2.category_id
          LEFT JOIN LATERAL (
            SELECT
              CONCAT(c1.category_id, '_', c2.category_id, '_', c3.category_id) AS path,
              cd3.name
            FROM oc_category c3
            LEFT JOIN oc_category_description cd3 ON cd3.category_id = c3.category_id
            WHERE c3.parent_id = c2.category_id AND cd3.language_id = 2 AND c3.status = 1
            ORDER BY c3.sort_order, cd3.name
          ) AS t3 ON true
          WHERE c2.parent_id = c1.category_id AND cd2.language_id = 2 AND c2.status = 1
          GROUP BY c2.category_id
          ORDER BY c2.sort_order, cd2.name
        ) AS t2 ON true
        WHERE c1.parent_id = 0 AND cd1.language_id = 2 AND c1.status = 1
        GROUP BY c1.category_id
        ORDER BY c1.sort_order, cd1.name
      ) AS t1
    ";
    return json_decode($this->db->query($sql)->row['value'], true);
  }
}
