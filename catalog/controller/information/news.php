<?
class ControllerInformationNews extends Controller {
  public function index() {
    $sql = "
      SELECT
        ep.ep_id AS id,
        epc.epc_title AS title,
        CONCAT('/image/', ep_preview_image) AS image
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) = 'news' AND epc.epc_language = 2
      ORDER by ep.ep_id DESC
    ";

    $data['news'] = $this->db->query($sql)->rows;
    foreach ($data['news'] as &$item) {
      $item['url'] = $this->url->link('information/news/read', ['news_id' => $item['id']]);
    }

    $data['headingH1'] = 'Новости';
    $this->document->setTitle("Новости от UKRMobil");
    $this->document->setDescription("Новости от UKRMobil ✅ Актуально ✅ Полезно");
    $this->document->setMicrodataBreadcrumbs();
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('information/news_list', $data));
  }

  public function read() {
    $newsId = $this->request->get['news_id'] ?? 0;

    $sql = "
      SELECT
        epc.epc_title AS title,
        epc.epc_content AS content,
        CONCAT('/image/', ep_preview_image) AS image,
        CONCAT(DATE_FORMAT(ep.ep_date_create, '%Y-%m-%dT%T'),
          DATE_FORMAT(TIMEDIFF(ep.ep_date_create, UTC_TIMESTAMP), '+%H:%i')) AS dateCreate,
        CONCAT(DATE_FORMAT(ep.ep_date_update, '%Y-%m-%dT%T'),
          DATE_FORMAT(TIMEDIFF(ep.ep_date_update, UTC_TIMESTAMP), '+%H:%i')) AS dateUpdate
      FROM ego_post_content epc
      LEFT JOIN ego_post ep ON ep.ep_id = epc.epc_post
      WHERE epc.epc_post = {$newsId} AND epc.epc_language = 2
      LIMIT 1;
    ";
    $post = $this->db->query($sql)->row;

    $data['headingH1'] = $post['title'] ?? '';
    $this->document->setTitle("{$data['headingH1']} - новости от UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ Новости от UKRMobil ✅ Актуально ✅ Полезно");
    $data['image'] = $post['image'] ?? '';
    $data['content'] = $post['content'] ?? '';

    $data['breadcrumb'] = [
      'name' => 'Новости',
      'link' => $this->url->link('information/news')
    ];
    $this->document->setMicrodataBreadcrumbs([$data['breadcrumb']]);

    $domain = ($_SERVER['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER);

    $microdata = [
      '@context'      => 'http://schema.org',
      '@type'         => 'NewsArticle',
      'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id'   => $this->url->link('information/news/read', ['news_id' => $newsId])
      ],
      'headline'      => $data['headingH1'],
      'image'         => ["{$domain}{$data['image']}"],
      'datePublished' => $post['dateCreate'],
      'dateModified'  => $post['dateUpdate'],
      'author' => [
        '@type' => 'Person',
        'name'  => 'UKRMOBIL'
      ],
      'publisher'     => [
        '@type' => 'Organization',
        'name'  => 'UKRMOBIL',
        'logo'  => [
          '@type' => 'ImageObject',
          'url'   => "{$domain}image/logo.png"
        ]
      ],
      'description'   => $data['headingH1']
    ];
    $this->document->setMicrodata(json_encode($microdata));

    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('information/news', $data));
  }
}
