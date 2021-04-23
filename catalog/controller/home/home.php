<?
class ControllerHomeHome extends Controller {
  public function index() {
    $data['headingH1'] = 'Запчасти для мобильных устройств';
    $title = 'Запчасти и оборудование для ремонта мобильных телефонов, планшетов, '
      . 'смарт-часов в Черновцах, Ровно, Украине в интернет-магазине UkrMobil';
    $description = 'Запчасти для мобильных телефонов, планшетов, смарт-часов '
      . '✅ Интернет-магазин UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине';
    $this->document->setTitle($title);
    $this->document->setDescription($description);

    $linkHome = $this->url->link('home/home');
    $linkLogo = $this->request->request['linkLogo'];
    $this->document->addMeta(['property' => 'og:title', 'content' => $title]);
    $this->document->addMeta(['property' => 'og:description', 'content' => $description]);
    $this->document->addMeta(['property' => 'og:url', 'content' => $linkHome]);
    $this->document->addMeta(['property' => 'og:image', 'content' => $linkLogo]);

    $this->document->addPreload('/resourse/images/home-sprite-icons.svg', 'image', 'image/svg+xml');
    $this->document->addLibStyle('/resourse/libs/swiper/swiper-bundle.min.css');
    $this->document->addPreload('/resourse/libs/swiper/swiper-bundle.min.js', 'script');
    $this->document->addLibScript('/resourse/libs/swiper/swiper-bundle.min.js');

    $this->document->addCustomStyle('/resourse/styles/home.min.css');
    $this->document->addPreload('/resourse/scripts/home.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/home.min.js');

    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');

    $data['navCategories'] = $this->load->controller('shared/components/nav_categories');
    $data['special'] = $this->load->controller('home/components/special');
    $data['benefits'] = $this->load->controller('home/components/benefits');
    $data['new'] = $this->load->controller('home/components/new');
    $data['promotions'] = $this->load->controller('home/components/promotions');
    $data['incomes'] = $this->load->controller('home/components/incomes');
    $data['news'] = $this->load->controller('home/components/news');

    $this->response->setOutput($this->load->view('home/home', $data));
  }
}
