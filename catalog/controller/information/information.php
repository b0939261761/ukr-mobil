<?
class ControllerInformationInformation extends Controller {
  public function index() {
    $informationId = $this->db->escape($this->request->get['information_id'] ?? '');

    $sql = "
      SELECT
        epc.epc_title AS title,
        epc.epc_content AS content
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) = '{$informationId}'
      LIMIT 1
    ";
    $post = $this->db->query($sql)->row;

    $data['headingH1'] = $post['title'] ?? '';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();
    $data['content'] = $post['content'] ?? '';
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('information/information', $data));
  }
}
