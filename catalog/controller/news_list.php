<?
class ControllerNewsList extends Controller {
  public function index() {

    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();

    $this->document->addPreload('/resourse/images/news_list-sprite-icons.svg', 'image', 'image/svg+xml');
    $this->document->addCustomStyle('/resourse/styles/news_list.min.css');

    $breacrumbsData = ['breadcrumbs' => [['name' => $data['headingH1']]]];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);
    $data['rightMenu'] = $this->load->controller('shared/components/right_menu', ['active' => $informationId]);
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('news_list/news_list', $data);
  }



  // public function index() {
  //   $sql = "
  //     SELECT
  //       ep.ep_id AS id,
  //       epc.epc_title AS title,
  //       CONCAT('/image/', ep_preview_image) AS image
  //     FROM ego_post ep
  //     LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
  //     WHERE LOWER(ep.ep_category) = 'news' AND epc.epc_language = 2
  //     ORDER by ep.ep_id DESC
  //   ";

  //   $data['news'] = $this->db->query($sql)->rows;
  //   foreach ($data['news'] as &$item) {
  //     $item['url'] = $this->url->link('information/news/read', ['news_id' => $item['id']]);
  //   }

  //   $data['headingH1'] = 'Новости';
  //   $title = 'Новости от UKRMobil';
  //   $description = 'Новости от UKRMobil ✅ Актуально ✅ Полезно';
  //   $this->document->setTitle($title);
  //   $this->document->setDescription($description);
  //   $this->document->setMicrodataBreadcrumbs();

  //   $linkNewsAll = $this->url->link('information/news');
  //   $linkLogo = $this->request->request['linkLogo'];

  //   $this->document->addMeta(['property' => 'og:title', 'content' => $title]);
  //   $this->document->addMeta(['property' => 'og:description', 'content' => $description]);
  //   $this->document->addMeta(['property' => 'og:url', 'content' => $linkNewsAll]);
  //   $this->document->addMeta(['property' => 'og:image', 'content' => $linkLogo]);

  //   $data['footer'] = $this->load->controller('common/footer');
  //   $data['header'] = $this->load->controller('common/header');
  //   $this->response->setOutput($this->load->view('information/news_list', $data));
  // }
}
