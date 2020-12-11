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

  private function getCategory($category) {
    $sql = "
      SELECT
        c.category_id AS id,
        cd.header_h1,
        meta_title,
        meta_description,
        cd.description
      FROM oc_category c
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE c.category_id = {$category} AND cd.language_id = 2 AND c.status = 1
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

  private function getSEO($category, $seoFiltersIds) {
    if (count($seoFiltersIds)) {
      $seoFiltersIdsImplode = implode($seoFiltersIds, ',');

      $sql = "
        SELECT
          sfd.category_id,
          COALESCE(f1.ord, 999999999) AS filter1Ord,
          COALESCE(f2.ord, 999999999) AS filter2Ord,
          sfd.filter1_id AS filter1Id,
          CONCAT(f1.queryKey, ' : ', f1.name) AS filter1Name,
          sfd.filter2_id AS filter2Id,
          CONCAT(f2.queryKey, ' : ', f2.name) AS filter2Name,
          sfd.headingH1,
          sfd.title,
          sfd.metaDescription,
          sfd.description
        FROM seo_filter_description sfd
        LEFT JOIN oc_category_path cp ON cp.category_id = sfd.category_id
        LEFT JOIN seo_filter_url f1 ON f1.id = sfd.filter1_id
        LEFT JOIN seo_filter_url f2 ON f2.id = sfd.filter2_id
        WHERE
          (
            sfd.category_id = 0
            AND sfd.filter1_id IN ({$seoFiltersIdsImplode})
            AND sfd.filter2_id = 0
          ) OR (
            sfd.category_id = {$category['id']}
            AND sfd.filter1_id IN ({$seoFiltersIdsImplode})
            AND sfd.filter2_id = 0
          ) OR (
            sfd.category_id = 0
            AND sfd.filter1_id IN ({$seoFiltersIdsImplode})
            AND sfd.filter2_id IN ({$seoFiltersIdsImplode})
          ) OR (
            sfd.category_id = {$category['id']}
            AND sfd.filter1_id IN ({$seoFiltersIdsImplode})
            AND sfd.filter2_id IN ({$seoFiltersIdsImplode})
          )
        ORDER BY cp.level DESC, category_id DESC, filter1Ord, filter2Ord
        LIMIT 1;
      ";

      $seoFilter = $this->db->query($sql)->row;
    }

    $filterText = '';
    if (!empty($this->request->request['filters'])){
      $filterNameList = array_map(function($item) { return $item['name']; }, $this->request->request['filters']);
      $filterText = implode(' , ', $filterNameList);
    }

    if (empty($seoFilter)) {
      $categoryNameList = array_map(function($item) { return $item['name']; }, $this->request->request['categories']);
      $headingH1Def = implode(' : ', $categoryNameList);

      if ($filterText) $headingH1Def .= " : {$filterText}";

      $headingH1 = $category['header_h1']
        ? str_replace('%filter%', $filterText, $category['header_h1'])
        : $headingH1Def;

      $title = $category['meta_title']
        ? str_replace('%filter%', $filterText, $category['meta_title'])
        : "{$headingH1Def} - купить в Черновцах, Ровно, Украине в интернет-магазине UKRMobil";

      $metaDescription = $category['meta_description']
        ? str_replace('%filter%', $filterText, $category['meta_description'])
        : "{$headingH1Def} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине";

      $description = '';
    } else {
      $headingH1 = str_replace('%filter%', $filterText, $seoFilter['headingH1']);
      $title = str_replace('%filter%', $filterText, $seoFilter['title']);
      $metaDescription = str_replace('%filter%', $filterText, $seoFilter['metaDescription']);
      $description = $seoFilter['description'];
    }

    return [
      'headingH1'       => $headingH1,
      'title'           => $title,
      'metaDescription' => $metaDescription,
      'description'     => $description
    ];
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

    $seoFiltersIds = [];
    $filters = $this->request->request['filters'];
    foreach ($filters as $filter) {
      $data['queryUrl'][$filter['key']] = $filter['value'];
      $seoFiltersIds[] = $filter['id'];
    }

    $category = $this->getCategory($data['queryUrl']['category']);
    if (!$category) $this->response->redirect($this->url->link('error/not_found'));

    $seo = $this->getSEO($category, $seoFiltersIds);
    $data['headingH1'] = $seo['headingH1'];
    $this->document->setTitle($seo['title']);
    $this->document->setDescription($seo['metaDescription']);


    $microdata = [
      "@context" => "https://schema.org/",
      "@type"    => "BreadcrumbList",
      "name"     => $data['headingH1'],
      "image" => [ $microdataImage ],
      "description" => $data['description'],
      "sku" => $data['product_id'],
      "offers" => [
        "@type" => "Offer",
        "url" => $productLink,
        "priceCurrency" => "UAH",
        "price" => $data['price_uah'],
        "priceValidUntil" => $priceValidUntil,
        "itemCondition" => "https://schema.org/NewCondition",
        "availability" => empty($data['productCount']) ? "https://schema.org/OutOfStock" : "https://schema.org/InStock"
      ]
    ];


{
"@context": "http://schema.org",
"@type": "BreadcrumbList",
"itemListElement":
[
{
"@type": "ListItem",
"position": 1,
"item":
{
"@id": "https://ukr-mobil.com/",
"name": "Главная"
}
},
{
"@type": "ListItem",
"position": 2,
"item":
{
"@id": "https://ukr-mobil.com/zapchasti_dlya_fotoaparatov_10000272",
"name": "Запчасти для фотоаппаратов"
}
}
]
}
8</script>


    if (count($filters) > 2) $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $products = $this->getProducts($data['queryUrl']);
    $data['products'] = $products['items'];
    $data['breadcrumbs'] = $this->getBreadcrumbs();

    $categoryDescription = '';
    if ($data['queryUrl']['page'] == 1) {
      if (!empty($seo['description'])) $categoryDescription = $seo['description'];
      elseif (empty($filters)) $categoryDescription = $category['description'];
    }

    $data['categoryDescription'] = html_entity_decode($categoryDescription);
    $data['productFilter'] = $this->load->controller('product/filter');
    $data['productCategories'] = $this->load->controller('product/categories');
    $data['pagination'] = $this->getPagination($products['pagination']);
    $data['isNotLastPage'] = $products['pagination']['isNotLastPage'];
    $this->document->setMicrodata(json_encode($microdata));
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
