<?
class ControllerSharedComponentsRightMenu extends Controller {
  public function index($props) {
    $data['menu'] = [
      'about' => [
        'name' => 'Про нас',
        'link' => $this->url->link('about'),
        'icon' => 'info'
      ],
      'news' => [
        'name' => 'Новини',
        'link' => $this->url->link('news_list'),
        'icon' => 'news'
      ],
      'tracking' => [
        'name' => 'Сервіс',
        'link' => $this->url->link('tracking'),
        'icon' => 'service'
      ],
      'delivery' => [
        'name' => 'Доставка і оплата',
        'link' => $this->url->link('information', ['information_id' => 'delivery']),
        'icon' => 'delivery'
      ],
      'warranty' => [
        'name' => 'Гарантія',
        'link' => $this->url->link('information', ['information_id' => 'warranty']),
        'icon' => 'warranty'
      ],
      'offer' => [
        'name' => 'Договір оферти',
        'link' => $this->url->link('information', ['information_id' => 'offer']),
        'icon' => 'docs-full'
      ]
    ];

    if (isset($props['active']) && isset($data['menu'][$props['active']])) $data['menu'][$props['active']]['active'] = true;
    return $this->load->view('shared/components/right_menu/right_menu', $data);
  }
}
