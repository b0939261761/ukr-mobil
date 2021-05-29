<?
class ControllerService extends Controller {
  public function index() {
    $data['headingH1'] = 'Упс, щось пішло не так!';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);

    $this->document->addLibStyle('/resourse/libs/swiper/swiper-bundle.min.css');
    $this->document->addPreload('/resourse/libs/swiper/swiper-bundle.min.js', 'script');
    $this->document->addLibScript('/resourse/libs/swiper/swiper-bundle.min.js');

    $this->document->addCustomStyle('/resourse/styles/404.min.css');
    $this->document->addPreload('/resourse/scripts/404.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/404.min.js');

    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');

    http_response_code(404);
    echo $this->load->view('404/404', $data);
  }
}
