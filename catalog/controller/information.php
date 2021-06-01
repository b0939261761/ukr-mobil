<?
class ControllerInformation extends Controller {
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
    $data['content'] = $post['content'] ?? '';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();

    $this->document->addCustomStyle('/resourse/styles/information.min.css');

    $breacrumbsData = ['breadcrumbs' => [['name' => $data['headingH1']]]];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);
    $data['rightMenu'] = $this->load->controller('shared/components/right_menu', ['active' => $informationId]);
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('information/information', $data);
  }
}
