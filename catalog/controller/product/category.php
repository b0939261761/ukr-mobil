<?
class ControllerProductCategory extends Ego\Controllers\BaseController {
  private function getPagination($data) {
    $pagination = new Pagination();
    $pagination->total = $data['total'];
    $pagination->page = $data['page'];
    $pagination->limit = $data['limit'];
    $pagination->url = $data['url'];
    return $pagination->render();
  }

  private function getProducts($data) {
    return (new \Ego\Providers\ProductFilterProvider($this->registry))->filter($data);
  }

  private function getBreadcrumbs($categories) {
    $breadcrumbs = [];
    $path = [];
    foreach ($categories as $category) {
      $path[] = $category['category_id'];
      $breadcrumbs[] = [
        'text' => $category['name'],
        'href' => $this->url->link('product/category', ['path' => implode('_', $path)])
      ];
    }
    return $breadcrumbs;
  }

  private function getSEO($category) {
    $categoryNameList = array_map(function($item) { return $item['name']; }, $this->request->request['categories']);
    $headingTitleDef = implode(' : ', $categoryNameList);

    $filterText = '';
    if (!empty($this->request->request['filters'])){
      $filterNameList = array_map(function($item) { return $item['name']; }, $this->request->request['filters']);
      $filterText = implode(' , ', $filterNameList);
      $headingTitleDef .= " : {$filterText}";
    }

    $headingTitle = empty($category['header_h1'])
      ? $headingTitleDef
      : str_replace('%filter%', $filterText, $category['header_h1']);

    $title = empty($category['meta_title'])
      ? "{$headingTitleDef} - купить в Черновцах, Ровно, Украине в интернет-магазине UKRMobil"
      : str_replace('%filter%', $filterText, $category['meta_title']);

    $description = empty($category['meta_description'])
      ? "{$headingTitleDef} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине"
      : str_replace('%filter%', $filterText, $category['meta_description']);


    return [
      'headingTitle' => $headingTitle,
      'title' => $title,
		  'description' => $description
    ];
  }

  public function index() {
    $this->load->model('catalog/category');

    $data['queryUrl'] = [
      'route' => $this->request->get['route'],
      'search' => $this->request->get['search'] ?? '',
      'path' => $this->request->get['path'] ?? '',
      'category' => (int)($this->request->request['category'] ?? 0),
      'available' => (int)($this->request->get['available'] ?? 0),
      'page' => (int)($this->request->get['page'] ?? 1)
    ];

    $filters = $this->request->request['filters'];
    foreach ($filters as $filter) $data['queryUrl'][$filter['key']] = $filter['value'];

    $category = $this->model_catalog_category->getCategory($data['queryUrl']['category']);
    if (!$category) $this->response->redirect($this->url->link('error/not_found'));

    $seo = $this->getSEO($category);
    $this->document->setTitle($seo['title']);
    $this->document->setDescription($seo['description']);
    if (count($filters) > 2) $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $data['headingTitle'] = $seo['headingTitle'];

    $products = $this->getProducts($data['queryUrl']);
    $data['products'] = $products['items'];
    $data['breadcrumbs'] = $this->getBreadcrumbs($this->request->request['categories']);
    $data['categoryDescription'] = html_entity_decode($category['description']);
    $data['productFilter'] = $this->load->controller('product/filter');
    $data['productCategories'] = $this->load->controller('product/categories');
    $data['pagination'] = $this->getPagination($products['pagination']);
    $data['isNotLastPage'] = $products['pagination']['isNotLastPage'];
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('product/category', $data));
  }

  public function apiGetList() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $products = $this->getProducts($requestData);

    $this->response->setOutput(json_encode([
      'products' => $products['items'],
      'pagination' => $this->getPagination($products['pagination']),
      'isNotLastPage' => $products['pagination']['isNotLastPage']
    ]));
  }
}
