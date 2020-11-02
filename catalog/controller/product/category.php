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

  private function getCategory($category_id) {
    $sql = "
      SELECT
        cd.header_h1,
        meta_title,
        meta_description,
        cd.description
      FROM oc_category c
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE c.category_id = {$category_id} AND cd.language_id = 2 AND c.status = 1
    ";
    return $this->db->query($sql)->row;
  }

  private function getProducts($data) {
    return (new \Ego\Providers\ProductFilterProvider($this->registry))->filter($data);
  }

  private function getBreadcrumbs() {
    $breadcrumbs = [];
    $path = [];
    foreach ($this->request->request['categories'] as $category) {
      $path[] = $category['category_id'];
      $breadcrumb = ['text' => $category['name']];

      if ($category['category_id'] != $this->request->request['category']) {
        $breadcrumb['link'] = $this->url->link('product/category', ['path' => implode('_', $path)]);
      }

      $breadcrumbs[] = $breadcrumb;
    }
    return $breadcrumbs;
  }

  private function getSEO($category) {
    $categoryNameList = array_map(function($item) { return $item['name']; }, $this->request->request['categories']);
    $headingH1Def = implode(' : ', $categoryNameList);

    $filterText = '';
    if (!empty($this->request->request['filters'])){
      $filterNameList = array_map(function($item) { return $item['name']; }, $this->request->request['filters']);
      $filterText = implode(' , ', $filterNameList);
      $headingH1Def .= " : {$filterText}";
    }

    $headingH1 = empty($category['header_h1'])
      ? $headingH1Def
      : str_replace('%filter%', $filterText, $category['header_h1']);

    $title = empty($category['meta_title'])
      ? "{$headingH1Def} - купить в Черновцах, Ровно, Украине в интернет-магазине UKRMobil"
      : str_replace('%filter%', $filterText, $category['meta_title']);

    $description = empty($category['meta_description'])
      ? "{$headingH1Def} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине"
      : str_replace('%filter%', $filterText, $category['meta_description']);

    return [
      'headingH1' => $headingH1,
      'title' => $title,
		  'description' => $description
    ];
  }

  public function index() {

    file_put_contents('./catalog/controller/product/__LOG__.json', "-----------\n" . json_encode($this->request)."\n\n", FILE_APPEND);


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

    $category = $this->getCategory($data['queryUrl']['category']);
    if (!$category) $this->response->redirect($this->url->link('error/not_found'));

    $seo = $this->getSEO($category);
    $data['headingH1'] = $seo['headingH1'];
    $this->document->setTitle($seo['title']);
    $this->document->setDescription($seo['description']);
    if (count($filters) > 2) $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $products = $this->getProducts($data['queryUrl']);
    $data['products'] = $products['items'];
    $data['breadcrumbs'] = $this->getBreadcrumbs();
    $data['categoryDescription'] = $data['queryUrl']['page'] == 1 ? html_entity_decode($category['description']) : '';
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
