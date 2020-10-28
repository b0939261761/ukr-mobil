<?php
use Ego\Controllers\BaseController;
use Ego\Providers\Util;

class ControllerProductSearch extends BaseController {
  private function getPathUrl($categoryId) {
    $sql = "
      SELECT c.category_id FROM oc_category_path cp
      LEFT JOIN oc_category c ON c.category_id = cp.path_id
	    LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
	    WHERE cp.category_id = :categoryId AND cd.language_id = 2 AND c.status = 1
      ORDER BY level DESC
    ";

    $baseModel = new \Ego\Models\BaseModel();
    $dataQuery = $baseModel->_getDb()->prepare($sql);
    $dataQuery->bindValue(':categoryId', $categoryId, \PDO::PARAM_INT);
    $dataQuery->execute();

    $paths = [];
    foreach ($dataQuery->fetchAll() as $item) $paths[] = (int)$item['category_id'];
    return implode('_', $paths);
  }

	private function getPagination($data, $total) {
		$config_theme = $this->config->get('config_theme');
		$url = "page={page}";
    if (!empty($categoryId)) {
      $url .= "&category={$categoryId}";
      $path = $this->getPathUrl($categoryId);
      if (!empty($path)) $url .= "&path={$path}";
    }
		if (!empty($data['search'])) $url .= "&search={$data['search']}";
		if (!empty($data['modelId'])) $url .= "&model={$data['modelId']}";
		if (!empty($data['brandId'])) $url .= "&brand={$data['brandId']}";
		if (!empty($data['stockId'])) $url .= "&stock={$data['stockId']}";
		if (!empty($data['isAvailable'])) $url .= "&available={$data['isAvailable']}";

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $data['page'];
		$pagination->limit = $this->config->get("theme_{$config_theme}_product_limit");
		$pagination->url = $this->url->link('product/search', $url);

		return $pagination->render();
	}

	private function getProducts($data) {
		$productFilterProvider = new \Ego\Providers\ProductFilterProvider();
		$productFilterProvider->setRegistry($this->registry);
		return $productFilterProvider->filter($data);
  }

	private function getParams($data) {
    $search = str_replace('\\', '', Util::getArrItem($data, 'search', ''));
    $categoryId = (int)Util::getArrItem($data, 'category', 0);
		$modelId = (int)Util::getArrItem($data, 'model', 0);
		$brandId = (int)Util::getArrItem($data, 'brand', 0);
		$stockId = (int)Util::getArrItem($data, 'stock', 0);
		$isAvailable = (int)Util::getArrItem($data, 'available', 0);
		$page = (int)Util::getArrItem($data, 'page', 1);

    if (empty($categoryId)) $categoryId = $this->request->request['category'] ?? 0;;

		return [
			'search' => $search,
			'modelId' => $modelId,
			'brandId' => $brandId,
			'stockId' => $stockId,
			'isAvailable' => $isAvailable,
			'categoryId' => $categoryId,
			'page' => $page
		];
	}

	private function getBreadcrumbUrl($data) {
		$url = '';

    if (!empty($categoryId)) {
      $path = $this->getPathUrl($categoryId);
      if (!empty($path)) $url .= "&path={$path}";
    }
		if (!empty($data['page'])) $url .= "&page={$data['page']}";
		if (!empty($data['search'])) $url .= "&search={$data['search']}";
		if (!empty($data['modelId'])) $url .= "&model={$data['modelId']}";
		if (!empty($data['brandId'])) $url .= "&brand={$data['brandId']}";
		if (!empty($data['stockId'])) $url .= "&stock={$data['stockId']}";
		if (!empty($data['isAvailable'])) $url .= "&available={$data['isAvailable']}";

		return $this->url->link('product/search', $url);
	}

	public function index() {
		$this->load->model('catalog/category');
		// $this->load->model('catalog/product');

    // $data['column_left'] = $this->load->controller('common/column_left');
		// $data['column_right'] = $this->load->controller('common/column_right');
		// $data['content_top'] = $this->load->controller('common/content_top');
		// $data['content_bottom'] = $this->load->controller('common/content_bottom');

		$params = $this->getParams($this->request->get);

		$data['heading_title'] = 'Поиск';
		if (!empty($params['search'])) $data['heading_title'] = "{$data['heading_title']} - {$params['search']}";
		$this->document->setTitle($data['heading_title']);
		$this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

		$products = $this->getProducts($params);

		$data['products'] = $products['items'];
		$data['pagination'] = $this->getPagination($params, $products['total']);
		$data['breadcrumbUrl'] = $this->getBreadcrumbUrl($params);
		$data['productCategories'] = $this->load->controller('product/categories');
		$data['product_filter'] = $this->load->controller('product/filter', $params);
		$data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('product/search', $data));
	}
}
