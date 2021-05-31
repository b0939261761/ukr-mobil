<?
class ControllerAbout extends Controller {
  public function index() {
    $data['headingH1'] = 'Компанія «UkrMobil»';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();

    $this->document->addPreload('/resourse/images/about-sprite-icons.svg', 'image', 'image/svg+xml');
    $this->document->addCustomStyle('/resourse/styles/about.min.css');

    $breacrumbsData = ['breadcrumbs' => [['name' => 'Про нас']]];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);
    $data['rightMenu'] = $this->load->controller('shared/components/right_menu', ['active' => 'about']);
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('about/about', $data);
  }
}
