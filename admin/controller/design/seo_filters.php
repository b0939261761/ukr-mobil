<?
// file_put_contents('./controller/design/__LOG__.txt', $sql);
class ControllerDesignSeoFilters extends Controller {
  public function index() {
    $page = (int)($this->request->get['page'] ?? 1);
    $data['selected'] = $this->request->post['selected'] ?? [];

    if ($page != 1) $url['page'] = $page;

    $userToken = $this->session->data['user_token'];
    $url['user_token'] = $userToken;

    $data['breadcrumbs'][] = [
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', ['user_token' => $userToken])
    ];

    $data['breadcrumbs'][] = [
      'text' => 'SEO Фильтр',
      'href' => $this->url->link('design/seo_filters', $url)
    ];

    $data['add'] = $this->url->link('design/seo_filters/edit', $url);
    $data['delete'] = $this->url->link('design/seo_filters/delete', $url);

    $data['success'] = $this->session->data['success'] ?? '';
    unset($this->session->data['success']);

    $limit = $this->config->get('config_limit_admin');
    $start = ($page - 1) * $limit;

    $sql = "
      SELECT
        sfd.id,
        cd.name AS categoryName,
        CONCAT(f1.queryKey, ' : ', f1.name) AS filter1Name,
        CONCAT(f2.queryKey, ' : ', f2.name) AS filter2Name
      FROM seo_filter_description sfd
      LEFT JOIN seo_filter_url f1 ON f1.id = sfd.filter1_id
      LEFT JOIN seo_filter_url f2 ON f2.id = sfd.filter2_id
      LEFT JOIN LATERAL (
        SELECT GROUP_CONCAT(cd.name SEPARATOR ' > ') AS name FROM oc_category_path cp
        LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
        WHERE cp.category_id = sfd.category_id
        ORDER BY cp.level
      ) AS cd ON true
      ORDER BY categoryName, filter1Name, filter2Name
      LIMIT {$start}, {$limit}
    ";

    $data['seoFilters'] = $this->db->query($sql)->rows;

    foreach($data['seoFilters'] as &$item) {
      $editLink = array_merge(['id' => $item['id']], $url);
      $item['link'] = $this->url->link('design/seo_filters/edit', $editLink);
    }

    $sql = "SELECT COUNT(*) AS count FROM seo_filter_description";
    $total = $this->db->query($sql)->row['count'];

    $pagination = new Pagination();
    $pagination->total = $total;
    $pagination->page = $page;
    $pagination->limit = $limit;
    $pagination->url = $this->url->link('design/seo_url', array_merge($url, ['page' => '{page}']));
    $data['pagination'] = $pagination->render();

    $start += 1;
    $end = $start + $limit;
    if ($end > $total) $end = $total;
    $pages = ceil($total / $limit);
    $data['results'] = "Показано с {$start} по {$end} из {$total} (страниц: {$pages})";

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('design/seo_filters_list', $data));
  }

  public function edit() {
    $this->document->setTitle('SEO Фильтр');
    $page = (int)($this->request->get['page'] ?? 1);
    if ($page != 1) $url['page'] = $page;

    $userToken = $this->session->data['user_token'];
    $url['user_token'] = $userToken;

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
      $id = (int)($this->request->post['id'] ?? 0);
      $categoryId = (int)($this->request->post['categoryId'] ?? 0);
      $filter1Id = (int)($this->request->post['filter1Id'] ?? 0);
      $filter2Id = (int)($this->request->post['filter2Id'] ?? 0);

      $headingH1 = $this->db->escape(($this->request->post['headingH1'] ?? ''));
      $title = $this->db->escape(($this->request->post['title'] ?? ''));
      $metaDescription = $this->db->escape(($this->request->post['metaDescription'] ?? ''));
      $description = $this->db->escape(($this->request->post['description'] ?? ''));

      $sql = "
        INSERT INTO seo_filter_description (
          category_id, filter1_id, filter2_id, title, headingH1, metaDescription, description
        ) VALUES (
          {$categoryId}, {$filter1Id}, {$filter2Id}, '{$title}', '{$headingH1}',
          '{$metaDescription}', '{$description}'
        ) AS new
        ON DUPLICATE KEY UPDATE
          title = new.title, headingH1 = new.headingH1,
          metaDescription = new.metaDescription, description = new.description
      ";
      $this->db->query($sql);

      if ($id && $this->db->getLastId() != $id) {
        $this->db->query("DELETE FROM seo_filter_description WHERE id = {$id}");
      }

      $this->session->data['success'] = 'Настройки успешно изменены!';
      $this->response->redirect($this->url->link('design/seo_filters', $url));
    }

    $data['breadcrumbs'][] = [
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', ['user_token' => $userToken])
    ];

    $data['breadcrumbs'][] = [
      'text' => 'SEO Фильтр',
      'href' => $this->url->link('design/seo_filters', $url)
    ];

    $data['id'] = (int)($this->request->get['id'] ?? 0);
    $data['headingForm'] = $data['id'] ? 'Редактировать' : 'Добавить';
    $data['action'] = $this->url->link('design/seo_filters/edit', $url);
    $data['cancel'] = $this->url->link('design/seo_filters', $url);

    if ($data['id'] && $this->request->server['REQUEST_METHOD'] != 'POST') {
      $sql = "
        SELECT
          sfd.category_id AS categoryId,
          cd.name AS categoryName,
          sfd.filter1_id AS filter1Id,
          CONCAT(f1.queryKey, ' : ', f1.name) AS filter1Name,
          sfd.filter2_id AS filter2Id,
          CONCAT(f2.queryKey, ' : ', f2.name) AS filter2Name,
          sfd.headingH1,
          sfd.title,
          sfd.metaDescription,
          sfd.description
        FROM seo_filter_description sfd
        LEFT JOIN seo_filter_url f1 ON f1.id = sfd.filter1_id
        LEFT JOIN seo_filter_url f2 ON f2.id = sfd.filter2_id
        LEFT JOIN LATERAL (
          SELECT GROUP_CONCAT(cd.name SEPARATOR ' > ') AS name FROM oc_category_path cp
          LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
          WHERE cp.category_id = sfd.category_id
          ORDER BY cp.level
        ) AS cd ON true
        WHERE sfd.id = {$data['id']}
      ";
      $seoFilters = $this->db->query($sql)->row;
    }

    $data['categoryId'] = $this->request->post['categoryId'] ?? $seoFilters['categoryId'] ?? '';
    $data['categoryName'] = $this->request->post['categoryName'] ?? $seoFilters['categoryName'] ?? '';
    $data['filter1Id'] = $this->request->post['filter1Id'] ?? $seoFilters['filter1Id'] ?? '';
    $data['filter1Name'] = $this->request->post['filter1Name'] ?? $seoFilters['filter1Name'] ?? '';
    $data['filter2Id'] = $this->request->post['filter2Id'] ?? $seoFilters['filter2Id'] ?? '';
    $data['filter2Name'] = $this->request->post['filter2Name'] ?? $seoFilters['filter2Name'] ?? '';
    $data['headingH1'] = $this->request->post['headingH1'] ?? $seoFilters['headingH1'] ?? '';
    $data['title'] = $this->request->post['title'] ?? $seoFilters['title'] ?? '';
    $data['metaDescription'] = $this->request->post['metaDescription'] ?? $seoFilters['metaDescription'] ?? '';
    $data['description'] = $this->request->post['description'] ?? $seoFilters['description'] ?? '';

    $this->document->setTitle('SEO Фильтр');
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('design/seo_filters_form', $data));
  }

  public function delete() {
    $url = [];
    $page = (int)($this->request->get['page'] ?? 1);
    if ($page != 1) $url['page'] = $page;

    $userToken = $this->session->data['user_token'];
    $url['user_token'] = $userToken;

    $selected = $this->request->post['selected'] ?? [];
    if (count($selected)) {
      foreach ($selected as $id) {
        $this->db->query("DELETE FROM seo_filter_description WHERE id = {$this->db->escape($id)}");
      }
      $this->session->data['success'] = 'Настройки успешно изменены!';
    }
    $this->response->redirect($this->url->link('design/seo_filters', $url));
  }

  public function categoryAutocomplete() {
    $sql = "
      SELECT * FROM (
        SELECT
          cp.category_id AS id,
          GROUP_CONCAT(cd.name ORDER BY cp.level SEPARATOR ' > ') AS name
        FROM oc_category c
        LEFT JOIN oc_category_path cp ON cp.category_id = c.category_id
        LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
        GROUP BY c.category_id
        ORDER BY name
      ) AS t
      WHERE name LIKE '%{$this->db->escape(html_entity_decode($this->request->get['name'] ?? ''))}%'
      LIMIT 5;
    ";
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($this->db->query($sql)->rows));
  }

  public function filterAutocomplete() {
    $sql = "
      SELECT * FROM (
        SELECT id, CONCAT(queryKey, ' : ', name) AS name FROM seo_filter_url ORDER BY name
      ) AS t
      WHERE name LIKE '%{$this->db->escape(html_entity_decode($this->request->get['name'] ?? ''))}%'
      LIMIT 5;
    ";
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($this->db->query($sql)->rows));
  }
}
