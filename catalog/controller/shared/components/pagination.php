<?
class ControllerSharedComponentsPagination extends Controller {
  public function index($props) {
    $maxPages = 5;
    $page = $props['page'] ?? 1;
    $totalPages = ceil($props['total'] / $props['limit']);
    if ($totalPages <= 1) return '';

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
      $props['queryUrl']['page'] = $i;
      $data['items'][$i] = $page == $i ? null : $this->url->link($props['routeUrl'], $props['queryUrl']);
    }

    if ($page > 1) $data['prev'] = $data['items'][$page - 1];
    if ($page < $totalPages) $data['next'] = $data['items'][$page + 1];

    return $this->load->view('shared/components/pagination/pagination', $data);
  }
}
