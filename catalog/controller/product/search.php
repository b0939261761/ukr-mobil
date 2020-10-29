<?
class ControllerProductSearch extends Controller {
  private function getPagination($data) {
    $pagination = new Pagination();
    $pagination->total = $data['total'];
    $pagination->page = $data['page'];
    $pagination->limit = $data['limit'];
    $pagination->url = $data['url'];
    return $pagination->render();
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

    $data['headingH1'] = 'Поиск';
		if (!empty($data['queryUrl']['search'])) $data['headingH1'] .= " - {$data['queryUrl']['search']}";
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $products = (new \Ego\Providers\ProductFilterProvider($this->registry))->filter($data['queryUrl']);
    $data['products'] = $products['items'];
    $data['breadcrumbs'] = [['text' => 'Поиск']];
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
