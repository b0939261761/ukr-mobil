<?
class ControllerNewsList extends Controller {
  private $limit = 8;

  public function index() {
    $page = (int)($this->request->get['page'] ?? 1);

    $data['headingH1'] = 'Новини';
    $title = 'Новини від UKRMobil';
    $description = 'Новости от UKRMobil ✅ Актуально ✅ Полезно';
    $this->document->setTitle($title);
    $this->document->setDescription($description);
    $this->document->setMicrodataBreadcrumbs();

    $this->document->addMeta(['property' => 'og:title', 'content' => $title]);
    $this->document->addMeta(['property' => 'og:description', 'content' => $description]);
    $this->document->addMeta(['property' => 'og:url', 'content' => $this->url->link('news_list')]);
    $this->document->addMeta(['property' => 'og:image', 'content' => $this->main->getLinkLogo()]);

    $this->document->addCustomStyle('/resourse/styles/news_list.min.css');
    $this->document->addPreload('/resourse/scripts/news-list.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/news_list.min.js');

    $data['news'] = $this->getNewsList($page);

    if (count($data['news'])) {
      $total = $this->getNewsTotal();
      $data['pagination'] = $this->getPagination($total, $page);
      $data['countLoadItems'] = $this->getCountLoadItens($total, $page);
    }

    $breacrumbsData = ['breadcrumbs' => [['name' => $data['headingH1']]]];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);
    $data['rightMenu'] = $this->load->controller('shared/components/right_menu', ['active' => 'news']);
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('news_list/news_list', $data);
  }

  private function getNewsList($page) {
    $start = ($page - 1) * $this->limit;

    $sql = "
      SELECT
        ep.ep_id AS id,
        DATE_FORMAT(ep.ep_date_update, '%d.%m.%Y') AS date,
        epc.epc_title AS title,
        COALESCE(ep.ep_preview_image, 'placeholder.jpg') AS image,
        ep.description
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) = 'news'
      ORDER by ep.ep_id DESC
      LIMIT {$this->limit} OFFSET {$start}
    ";

    $news = $this->db->query($sql)->rows;

    foreach ($news as &$item) {
      $item['url'] = $this->url->link('news', ['news_id' => $item['id']]);
      $item['image'] = $this->image->resize($item['image'], 302, 180);
    }
    return $news;
  }

  private function getNewsTotal() {
    $sql = "
      SELECT COUNT(*) AS total
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) = 'news'
    ";
    return $this->db->query($sql)->row['total'];
  }

  private function getCountLoadItens($total, $page) {
    $countLoadItems = $total - $page * $this->limit;
    if ($countLoadItems < 1) return 0;
    elseif ($countLoadItems < $this->limit) return $countLoadItems;
    else return $this->limit;
  }

  private function getPagination($total, $page) {
    $padinationData = [
      'page'     => $page,
      'total'    => $total,
      'limit'    => $this->limit,
      'routeUrl' => 'news_list'
    ];
    return $this->load->controller('shared/components/pagination', $padinationData);
  }

  public function loadMore() {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $page = (int)($requestData['page'] ?? 1);

    $items = $this->getNewsList($page);

    if (count($items)) {
      $total = $this->getNewsTotal();
      $pagination = $this->getPagination($total, $page);
      $countLoadItems = $this->getCountLoadItens($total, $page);
    }

    echo json_encode([
      'items'          => $items,
      'pagination'     => $pagination ?? '',
      'countLoadItems' => $countLoadItems ?? 0
    ]);
  }
}

