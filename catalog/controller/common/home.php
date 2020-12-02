<?
class ControllerCommonHome extends Controller {
  public function index() {
    $data['headingH1'] = 'Запчасти для мобильных устройств';
    $this->document->setTitle('Запчасти и оборудование для ремонта мобильных телефонов, планшетов, смарт-часов в Черновцах, Ровно, Украине в интернет-магазине UkrMobil');
    $this->document->setDescription('Запчасти для мобильных телефонов, планшетов, смарт-часов ✅ Интернет-магазин UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине');
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $data['product_new'] = $this->load->controller('home/product_new');
    $data['product_stocks'] = $this->load->controller('home/product_stocks');
    $data['news'] = $this->load->controller('home/news');
    $this->response->setOutput($this->load->view('common/home', $data));
  }
}
