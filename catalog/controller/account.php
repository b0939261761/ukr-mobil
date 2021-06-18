<?
class ControllerAccount extends Controller {
  public function index() {
    $this->document->addCustomStyle('/resourse/styles/account.min.css');
    $this->document->addPreload('/resourse/scripts/account.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/account.min.js');


    $data['headingH1'] = 'Особистий кабінет';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();

    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs');
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('account/account', $data);
  }
}

