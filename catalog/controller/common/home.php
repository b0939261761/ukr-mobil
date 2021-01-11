<?
class ControllerCommonHome extends Controller {
  public function index() {
    $data['headingH1'] = 'Запчасти для мобильных устройств';
    $title = 'Запчасти и оборудование для ремонта мобильных телефонов, планшетов, '
      . 'смарт-часов в Черновцах, Ровно, Украине в интернет-магазине UkrMobil';
    $description = 'Запчасти для мобильных телефонов, планшетов, смарт-часов '
      . '✅ Интернет-магазин UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине';
    $this->document->setTitle($title);
    $this->document->setDescription($description);

    $linkHome = $this->url->link('common/home');
    $linkLogo = $this->request->request['linkLogo'];
    $this->document->addMeta(['property' => 'og:title', 'content' => $title]);
    $this->document->addMeta(['property' => 'og:description', 'content' => $description]);
    $this->document->addMeta(['property' => 'og:url', 'content' => $linkHome]);
    $this->document->addMeta(['property' => 'og:image', 'content' => $linkLogo]);

    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $data['productNew'] = $this->load->controller('home/product_new');
    $data['productStocks'] = $this->load->controller('home/product_stocks');
    $data['news'] = $this->load->controller('home/news');
    $this->response->setOutput($this->load->view('common/home', $data));
  }
}
