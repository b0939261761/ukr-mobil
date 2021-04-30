<?
class ControllerCatalogComponentsBreadcrumbs extends Controller {
  public function index() {
    $data['breadcrumbs'] = $this->getBreadcrumbs();
    return $this->load->view('catalog/components/breadcrumbs/breadcrumbs', $data);
  }

  private function getBreadcrumbs() {
    $breadcrumbs = [];
    $path = [];
    $categories = $this->category->getCurrentCatagories();
    $count = count($categories) - 1;

    foreach ($categories as $index=>$category) {
      $path[] = $category['id'];
      $breadcrumb = ['name' => $category['name']];

      if ($index < $count) {
        $breadcrumb['link'] = $this->url->link('product/category', ['path' => implode('_', $path)]);
      }

      $breadcrumbs[] = $breadcrumb;
    }
    return $breadcrumbs;
  }
}



