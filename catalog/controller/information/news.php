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
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('information/news', $data));
  }

  public function read() {
    $newsId = $this->request->get['news_id'] ?? 0;

    $sql = "
      SELECT
        epc.epc_title AS title,
        epc.epc_content AS content,
        CONCAT('/image/', ep_preview_image) AS image
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
    $data['linkNews'] = $this->url->link('information/news');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('information/news_read', $data));
  }
}
