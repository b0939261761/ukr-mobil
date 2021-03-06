<?
class ControllerHomeComponentsNews extends Controller {
  public function index() {
    $data['news'] = $this->getNews();
    $data['linkNews'] = $this->url->link('news_list');
    return $this->load->view('home/components/news/news', $data);
  }

  private function getNews() {
    $sql = "
      SELECT
        ep.ep_id AS id,
        DATE_FORMAT(ep.ep_date_update, '%d.%m.%Y') AS date,
        epc.epc_title AS title,
        ep_preview_image AS image,
        CONCAT('/image/', ep_preview_image) AS image1
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) = 'news'
      ORDER by ep.ep_id DESC
      LIMIT 10
    ";

    $news = $this->db->query($sql)->rows;
    foreach ($news as &$item) {
      $item['link'] = $this->url->link('news', ['news_id' => $item['id']]);
      $item['image'] = $this->image->resize($item['image'], 386, 230);
    }
    return $news;
  }
}
