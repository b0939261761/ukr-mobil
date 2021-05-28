<?
class ControllerHomeComponentsSpecial extends Controller {
  public function index() {
    $sql = "
      SELECT link, enabled, IF(image = '', 'placeholder.jpg', image) AS image
      FROM banner
      WHERE banner_type = 'home' AND enabled ORDER BY ord";
    $banners = $this->db->query($sql)->rows;

    foreach ($banners as &$item) $item['image'] = $this->image->resize($item['image'], 1277, 395);
    $data['banners'] = $banners;

    return $this->load->view('home/components/special/special', $data);
  }
}
