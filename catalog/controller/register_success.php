<?
class ControllerRegisterSuccess extends Controller {
  public function index() {
    $data['headingH1'] = 'Реєстрація';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();


    $this->document->addLibStyle('/resourse/libs/swiper/swiper-bundle.min.css');
    $this->document->addPreload('/resourse/libs/swiper/swiper-bundle.min.js', 'script');
    $this->document->addLibScript('/resourse/libs/swiper/swiper-bundle.min.js');

    $this->document->addCustomStyle('/resourse/styles/register-success.min.css');
    $this->document->addPreload('/resourse/scripts/register-success.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/register-success.min.js');

    $data['linkAccount'] = $this->url->link('account');
    $data['sliderIncome'] = $this->load->controller('shared/components/slider_income');

    $breacrumbsData = ['breadcrumbs' => [['name' => $data['headingH1']]]];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('register_success/register_success', $data);
  }
}

