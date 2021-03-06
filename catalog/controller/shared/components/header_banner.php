<?
class ControllerSharedComponentsHeaderBanner extends Controller {
  public function index() {
    $sql = "
      SELECT link, enabled, backgroundColor, IF(image = '', 'placeholder.jpg', image) AS image
      FROM banner
      WHERE banner_type = 'header'";

    $banner = $this->db->query($sql)->row;
    if ($banner) $banner['image'] = $this->image->resize($banner['image']);
    $data['banner'] = $banner;

    return $this->load->view('shared/components/header_banner/header_banner', $data);
  }
}
