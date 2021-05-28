<?
class ControllerCatalogComponentsCatalogPagination extends Controller {
  public function index() {
    $data = $this->getPagination();

    $itemPerPage = $this->catalog->getItemsPerPage();
    $countLoadItems = $this->catalog->getItemsTotal() - $this->catalog->getPage() * $itemPerPage;
    if ($countLoadItems < 1) $data['countLoadItems'] = 0;
    elseif ($countLoadItems < $itemPerPage) $data['countLoadItems'] = $countLoadItems;
    else $data['countLoadItems'] = $itemPerPage;

    return $this->load->view('catalog/components/catalog_pagination/catalog_pagination', $data);
  }

  private function getPagination() {
    $maxPages = 5;
    $totalPages = ceil($this->catalog->getItemsTotal() / $this->catalog->getItemsPerPage());
    if ($totalPages <= 1) return [];

    $page = $this->catalog->getPage();

    if ($totalPages <= $maxPages) {
      $start = 1;
      $end = $totalPages;
    } else {
      $start = $page - floor($maxPages / 2);
      $end = $page + floor($maxPages / 2);

      if ($start < 1) {
        $end += abs($start) + 1;
        $start = 1;
      }

      if ($end > $totalPages) {
        $start -= $end - $totalPages;
        $end = $totalPages;
      }
    }

    $data = [];
    for ($i = $start; $i <= $end; $i++) {
      $queryUrl = [
        'search'   => $this->catalog->getSearch(),
        'path'     => $this->catalog->getPath(),
        'category' => $this->catalog->getCategoryId(),
        'sort'     => $this->catalog->getSort(),
        // 'available' => (int)($this->request->get['available'] ?? 0),
        'page' => $i
      ];

      $data['items'][$i] = $page == $i ? null : $this->url->link('catalog/catalog', $queryUrl);
    }

    if ($page > 1) $data['prev'] = $data['items'][$page-1];
    if ($page < $totalPages) $data['next'] = $data['items'][$page+1];

    return $data;
  }
}
