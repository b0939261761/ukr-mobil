<?
class ControllerDesignBanners extends Controller {
  public function index() {
    $this->document->setTitle('Баннера');
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $userToken = $this->session->data['user_token'];

    $data['breadcrumbs'] = [
      [
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/dashboard', ['user_token' => $userToken])
      ],
      [
        'text' => 'Баннера',
        'href' => $this->url->link('design/banners', ['user_token' => $userToken])
      ]
    ];

    $data['action'] = $this->url->link('design/banners', ['user_token' => $userToken]);

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
      $topBannerEnabled = (int)($this->request->post['topBannerEnabled'] ?? 0);
      $topBannerImage = $this->db->escape($this->request->post['topBannerImage'] ?? '');
      $topBannerLink = $this->db->escape($this->request->post['topBannerLink'] ?? '');
      $topBannerBackgroundColor = $this->db->escape($this->request->post['topBannerBackgroundColor'] ?? '');

      $this->db->query("DELETE FROM banner");

      $sql = "
        INSERT INTO banner (banner_type, link, image, enabled, backgroundColor)
        VALUES ('header', '{$topBannerLink}', '{$topBannerImage}', {$topBannerEnabled}, '{$topBannerBackgroundColor}');
      ";
      $this->db->query($sql);

      $homeOrd = 0;
      foreach ($this->request->post['homeBanners'] ?? [] as &$item) {
        $homeBannerEnabled = (int)($item['enabled'] ?? 0);
        $homeBannerImage = $this->db->escape($item['image'] ?? '');
        $homeBannerLink = $this->db->escape($item['link'] ?? '');
        $homeOrd += 1;

        $homeValues[] = "('home', '{$homeBannerLink}', '{$homeBannerImage}', {$homeBannerEnabled}, {$homeOrd})";
      }

      if (isset($homeValues)) {
        $sqlHomeValues = implode(',', $homeValues);
        $sql = "INSERT INTO banner (banner_type, link, image, enabled, ord) VALUES {$sqlHomeValues}";
        $this->db->query($sql);
      }

      $data['success'] = 'Сохранено!';
    }

    $sql = "SELECT link, enabled, IF(image = '', 'placeholder.png', image) AS image, backgroundColor
      FROM banner WHERE banner_type = 'header'";
    $topBanner = $this->db->query($sql)->row;

    if ($topBanner) $topBanner['thumb'] = $this->model_tool_image->resize($topBanner['image'], 100, 100);
    $data['topBanner'] = $topBanner;

    $sql = "SELECT link, IF(image = '', 'placeholder.png', image) AS image, enabled, ord
      FROM banner WHERE banner_type = 'home' ORDER BY ord";

    $homeBanners = $this->db->query($sql)->rows;

    foreach ($homeBanners as &$item) $item['thumb'] = $this->model_tool_image->resize($item['image'], 100, 100);
    $data['homeBanners'] = $homeBanners;

    $data['thumbDefault'] = $this->model_tool_image->resize('placeholder.png', 100, 100);

    $this->response->setOutput($this->load->view('design/banners', $data));
  }
}
