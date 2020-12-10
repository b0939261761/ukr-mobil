<?
class ControllerInformationSitemap extends Controller {
  public function index() {
    $sql = "
      SELECT cd.name, c.category_id as ID
      FROM oc_category c
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE parent_id = 0
    ";

    $categories = $this->db->query($sql)->rows;

    $data['headingH1'] = 'Карта сайта';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('information/sitemap', $data));
  }

  private function getPagination($page, $total, $limit) {
    $pagination = new Pagination();
    $pagination->page = $page;
    $pagination->total = $total;
    $pagination->limit = $limit;
    $pagination->url = $this->url->link('information/sitemap', ['page' => '{page}']);
    return $pagination->render();
  }

  private function getLinks($categories, $index, $start, $end) {
    $result = [];
    foreach ($categories as $category) {
      $queriesCategory = ['path' => $category['path']];
      $nameCategory = $category['name'];

      if ($index >= $start && $index <= $end) {
        $result[] = [
          'name' => $nameCategory,
          'link' => $this->url->link('product/category', $queriesCategory)
        ];
      }

      ++$index;

      foreach ($category['brands'] as $brand) {
        $queriesBrand = array_merge($queriesCategory, ['brand' => $brand['id']]);
        $nameBrand = "{$nameCategory} : {$brand['name']}";

        if ($index >= $start && $index <= $end) {
          $result[] = [
            'name' => $nameBrand,
            'link' => $this->url->link('product/category', $queriesBrand)
          ];
        }

        ++$index;

        foreach ($brand['models'] as $model) {
          $queriesModel = array_merge($queriesBrand, ['model' => $model['id']]);
          $nameModel = "{$nameBrand}, {$model['name']}";

          if ($index >= $start && $index <= $end) {
            $result[] = [
              'name' => $nameModel,
              'link' => $this->url->link('product/category', $queriesModel)
            ];
          }

          ++$index;
        }
      }

      if (isset($category['children'])) {
        $chidrenResult = $this->getLinks($category['children'], $index, $start, $end);
        $index = $chidrenResult['index'];
        if (!empty($chidrenResult['list'])) $result = array_merge($result, $chidrenResult['list']);
      }
    }

    return ['index' => $index, 'list' => $result];
  }

  private function getCategoryList() {
    $sql = "
      WITH
        tmpCatagoriesBrandsModels AS (
          SELECT
            cp.path_id AS category_id,
            m.brand_id,
            b.name AS brand_name,
            b.ord AS brand_ord,
            pm.model_id,
            m.name AS model_name,
            m.ord AS model_ord
          FROM products_models pm
          LEFT JOIN oc_product_to_category ptc ON ptc.product_id = pm.product_id
          LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
          LEFT JOIN models m ON m.id = pm.model_id
          LEFT JOIN brands b ON b.id = m.brand_id
          GROUP BY cp.path_id, m.brand_id, pm.model_id
        ),
        tmpGroupCategoriesBrands AS (
          SELECT DISTINCT category_id, brand_id, brand_name, brand_ord FROM tmpCatagoriesBrandsModels
        ),
        tmpModels AS (
          SELECT
            category_id,
            brand_id,
            brand_name,
            brand_ord,
            JSON_ARRAYAGG(JSON_OBJECT('id', t.id, 'name', t.name)) as models
          FROM tmpGroupCategoriesBrands tgcb,
          LATERAL (
            SELECT
              model_id AS id,
              model_name AS name
            FROM tmpCatagoriesBrandsModels
            WHERE category_id = tgcb.category_id AND brand_id = tgcb.brand_id
            ORDER BY model_ord, model_name
          ) AS t
          GROUP BY category_id, brand_id
        ),
        tmpGroupCategories AS (
          SELECT DISTINCT category_id FROM tmpCatagoriesBrandsModels
        ),
        tmpBrands AS (
          SELECT
            category_id,
            JSON_ARRAYAGG(JSON_OBJECT('id', t.id, 'name', t.name, 'models', t.models)) AS brands
          FROM tmpGroupCategories tgc,
          LATERAL (
            SELECT
              brand_id AS id,
              brand_name AS name,
              (SELECT models FROM tmpModels WHERE category_id = tm.category_id
                AND brand_id = tm.brand_id) AS models
            FROM tmpModels tm
            WHERE category_id = tgc.category_id
            ORDER BY brand_ord, brand_name
            ) AS t
          GROUP BY category_id
        )
      SELECT
        IF(count(path), JSON_ARRAYAGG(JSON_OBJECT('path', path, 'name', name, 'brands', brands, 'children', children)), JSON_ARRAY()) AS value
      FROM (
        SELECT
          c1.category_id AS path,
          cd1.name,
          COALESCE(tb1.brands, JSON_ARRAY()) AS brands,
          IF(count(t2.path), JSON_ARRAYAGG(JSON_OBJECT('path', t2.path, 'name', t2.name, 'brands', t2.`brands`, 'children', t2.children)), JSON_ARRAY()) AS children
          FROM oc_category c1
        LEFT JOIN oc_category_description cd1 ON cd1.category_id = c1.category_id
        LEFT JOIN tmpBrands tb1 ON tb1.category_id = c1.category_id
        LEFT JOIN LATERAL (
          SELECT
            CONCAT(c1.category_id, '_', c2.category_id) AS path,
            cd2.name,
            COALESCE(tb2.brands, JSON_ARRAY()) AS brands,
            IF(count(t3.path), JSON_ARRAYAGG(JSON_OBJECT('path', t3.path, 'name', t3.name, 'brands', t3.brands)), JSON_ARRAY()) AS children
          FROM oc_category c2
          LEFT JOIN oc_category_description cd2 ON cd2.category_id = c2.category_id
          LEFT JOIN tmpBrands tb2 ON tb2.category_id = c2.category_id
          LEFT JOIN LATERAL (
            SELECT
              CONCAT(c1.category_id, '_', c2.category_id, '_', c3.category_id) AS path,
              cd3.name,
              COALESCE(tb3.brands, JSON_ARRAY()) AS brands
            FROM oc_category c3
            LEFT JOIN oc_category_description cd3 ON cd3.category_id = c3.category_id
            LEFT JOIN tmpBrands tb3 ON tb3.category_id = c3.category_id
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
