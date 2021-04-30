<?
class ControllerHomeComponentsSpecial extends Controller {
  public function index() {
    $this->load->model('tool/image');

    $sql = "
      SELECT link, enabled, IF(image = '', 'placeholder.png', image) AS image
      FROM banner
      WHERE banner_type = 'home' AND enabled ORDER BY ord";
    $banners = $this->db->query($sql)->rows;

    foreach ($banners as &$item) $item['image'] = $this->model_tool_image->resize($item['image'], 1277, 395);
    $data['banners'] = $banners;

    return $this->load->view('home/components/special/special', $data);
  }
}
