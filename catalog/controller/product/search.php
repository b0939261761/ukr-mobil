<?
class ControllerProductSearch extends Ego\Controllers\BaseController {
  private function getPagination($data) {
    $pagination = new Pagination();
    $pagination->total = $data['total'];
    $pagination->page = $data['page'];
    $pagination->limit = $data['limit'];
    $pagination->url = $data['url'];
    return $pagination->render();
  }

  private function getBreadcrumbs($data) {
    $route = $data['route'];
    unset($data['route']);

    $query = [];
    foreach ($data as $key => $value) {
      if (!empty($value)) $query[$key] = $value;
    }

    return [[
      'text' => 'Поиск',
      'href' => $this->url->link($route, $query)
    ]];
  }

  public function index() {
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

    $data['heading_title'] = 'Поиск';
		if (!empty($params['search'])) $data['heading_title'] = "{$data['heading_title']} - {$params['search']}";
		$this->document->setTitle($data['heading_title']);
		$this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $products = (new \Ego\Providers\ProductFilterProvider($this->registry))->filter($data['queryUrl']);
    $data['products'] = $products['items'];
    $data['breadcrumbs'] = $this->getBreadcrumbs($data['queryUrl']);
    $data['categoryDescription'] = '';
    $data['productFilter'] = $this->load->controller('product/filter');
    $data['productCategories'] = $this->load->controller('product/categories');
    $data['pagination'] = $this->getPagination($products['pagination']);
    $data['isNotLastPage'] = $products['pagination']['isNotLastPage'];
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('product/category', $data));
  }
}
