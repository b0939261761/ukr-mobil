<?
class ControllerInformationNews extends Controller {
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

  // public function read() {
  //   $newsId = $this->request->get['news_id'] ?? 0;

  //   $sql = "
  //     SELECT
  //       epc.epc_title AS title,
  //       epc.epc_content AS content,
  //       CONCAT('/image/', ep_preview_image) AS image,
  //       CONCAT(DATE_FORMAT(ep.ep_date_create, '%Y-%m-%dT%T'),
  //         DATE_FORMAT(TIMEDIFF(ep.ep_date_create, UTC_TIMESTAMP), '+%H:%i')) AS dateCreate,
  //       CONCAT(DATE_FORMAT(ep.ep_date_update, '%Y-%m-%dT%T'),
  //         DATE_FORMAT(TIMEDIFF(ep.ep_date_update, UTC_TIMESTAMP), '+%H:%i')) AS dateUpdate
  //     FROM ego_post_content epc
  //     LEFT JOIN ego_post ep ON ep.ep_id = epc.epc_post
  //     WHERE epc.epc_post = {$newsId} AND epc.epc_language = 2
  //     LIMIT 1;
  //   ";
  //   $post = $this->db->query($sql)->row;

  //   $data['headingH1'] = $post['title'] ?? '';
  //   $title = "{$data['headingH1']} - новости от UKRMobil";
  //   $description = "{$data['headingH1']} ✅ Новости от UKRMobil ✅ Актуально ✅ Полезно";
  //   $this->document->setTitle($title);
  //   $this->document->setDescription( $description);
  //   $data['image'] = $post['image'] ?? '';
  //   $data['content'] = $post['content'] ?? '';

  //   $linkNewsAll = $this->url->link('information/news');

  //   $data['breadcrumb'] = ['name' => 'Новости', 'link' => $linkNewsAll];
  //   $this->document->setMicrodataBreadcrumbs([$data['breadcrumb']]);

  //   $linkNews = $this->url->link('information/news/read', ['news_id' => $newsId]);
  //   $domain = rtrim($this->request->request['canonical'], '/');
  //   $linkNewsImage = "{$domain}{$data['image']}";

  //   $microdata = [
  //     '@context'      => 'http://schema.org',
  //     '@type'         => 'NewsArticle',
  //     'mainEntityOfPage' => [
  //       '@type' => 'WebPage',
  //       '@id'   => $linkNews
  //     ],
  //     'headline'      => $data['headingH1'],
  //     'image'         => [$linkNewsImage],
  //     'datePublished' => $post['dateCreate'],
  //     'dateModified'  => $post['dateUpdate'],
  //     'author' => [
  //       '@type' => 'Person',
  //       'name'  => 'UKRMOBIL'
  //     ],
  //     'publisher'     => [
  //       '@type' => 'Organization',
  //       'name'  => 'UKRMOBIL',
  //       'logo'  => [
  //         '@type' => 'ImageObject',
  //         'url'   => $this->request->request['linkLogo']
  //       ]
  //     ],
  //     'description'   => $data['headingH1']
  //   ];
  //   $this->document->setMicrodata(json_encode($microdata));


  //   $this->document->addMeta(['property' => 'og:title', 'content' => $title]);
  //   $this->document->addMeta(['property' => 'og:description', 'content' => $description]);
  //   $this->document->addMeta(['property' => 'og:url', 'content' => $linkNews]);
  //   $this->document->addMeta(['property' => 'og:image', 'content' => $linkNewsImage]);

  //   $data['footer'] = $this->load->controller('common/footer');
  //   $data['header'] = $this->load->controller('common/header');
  //   $this->response->setOutput($this->load->view('information/news', $data));
  // }

}
