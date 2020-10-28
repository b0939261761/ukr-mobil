<?php
use Ego\Controllers\BaseController;
use Ego\Providers\Util;

class ControllerProductCategories extends BaseController {
  private function getCategories($categoryId) {
    $sql = "
      SELECT c.category_id, cd.name FROM oc_category c
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE c.parent_id = {$categoryId} AND cd.language_id = 2 AND c.status = 1
      ORDER BY c.sort_order, LCASE(cd.name)
    ";

    $baseModel = new \Ego\Models\BaseModel();
    $dataQuery = $baseModel->_getDb()->prepare($sql);
    $dataQuery->execute();
    return $dataQuery->fetchAll();
  }

	public function index() {
    $search = Util::getArrItem($this->request->get, 'search', '');
    $categoryId = $this->request->request['category'] ?? 0;

    $urlRoute = 'product/category';
		$searchParams = '';
		if (!empty($search)) {
      $urlRoute = 'product/search';
  		$searchParams = "&search={$search}";
		}

    $sql = "
      SELECT c.category_id FROM oc_category_path cp
      LEFT JOIN oc_category c ON c.category_id = cp.path_id
	    LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
	    WHERE cp.category_id = :categoryId AND cd.language_id = 2 AND c.status = 1
      ORDER BY level
    ";

    $baseModel = new \Ego\Models\BaseModel();
    $dataQuery = $baseModel->_getDb()->prepare($sql);
    $dataQuery->bindValue(':categoryId', $categoryId, \PDO::PARAM_INT);
    $dataQuery->execute();
    $categories = $dataQuery->fetchAll();


    $data['categories'] = [];

		foreach ($this->getCategories(0) as $child0) {
      $categoryId0 = $child0['category_id'];
      $childrenData0 = [];
      $active0 = false;

			if ($categoryId0 == ($categories[0]['category_id'] ?? 0)) {
        $active0 = true;

				foreach ($this->getCategories($categoryId0) as $child1) {
          $categoryId1 = $child1['category_id'];
          $childrenData1 = [];
          $active1 = false;

					if ($categoryId1 == ($categories[1]['category_id'] ?? 0)) {
            $active1 = true;

						foreach ($this->getCategories($categoryId1) as $child2) {
              $categoryId2 = $child2['category_id'];
              $active2 = false;
              if ($categoryId2 == ($categories[2]['category_id'] ?? 0)) $active2 = true;

              $params2 = "path={$categoryId0}_{$categoryId1}_{$categoryId2}{$searchParams}";
							$childrenData1[] = [
                'name' => $child2['name'],
                'active' => $active2,
								'href' => $this->url->link($urlRoute, $params2)
              ];
						}
					}

          $params1 = "path={$categoryId0}_{$categoryId1}{$searchParams}";
          $childrenData0[] = [
            'name' => $child1['name'],
            'children' => $childrenData1,
            'active' => $active1,
						'href' => $this->url->link($urlRoute, $params1)
					];
				}
			}

			$data['categories'][] = [
				'name' => $child0['name'],
        'children' => $childrenData0,
        'active' => $active0,
				'href' => $this->url->link($urlRoute, "path={$categoryId0}{$searchParams}")
      ];
		}

		return $this->load->view('product/categories', $data);
	}
}
