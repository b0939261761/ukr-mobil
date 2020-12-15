<?
class ControllerInformationAboutUs extends Controller {
  public function index() {
    $sql = "
      SELECT
        LOWER(ep.ep_category) AS category,
        epc.epc_title AS title,
        epc.epc_content AS content
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) in ('about_us', 'delivery', 'warranty', 'contact')
        AND epc.epc_language = 2
      ORDER by ep.ep_id DESC
    ";

    foreach ($this->db->query($sql)->rows as $item) {
      $data[$item['category']]['title'] = $item['title'];
      $data[$item['category']]['content'] = $item['content'];
    }

    $data['headingH1'] = 'О нас';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('information/about_us', $data));
  }

}
