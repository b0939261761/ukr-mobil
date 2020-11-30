<?
class ControllerHomeNews extends Controller {
  public function index() {
    $sql = "
      SELECT
        ep.ep_id AS id,
        DATE_FORMAT(ep.ep_date_update, '%d.%m.%Y') AS date,
        epc.epc_title AS title,
        CONCAT('/image/', ep_preview_image) AS image
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) = 'news' AND epc.epc_language = 2
      ORDER by ep.ep_id DESC
      LIMIT 10
    ";

    $data['news'] = $this->db->query($sql)->rows;
    foreach ($data['news'] as &$item) {
      $item['url'] = $this->url->link('information/news/read', ['news_id' => $item['id']]);
    }

    return $this->load->view('home/news', $data);
  }
}
