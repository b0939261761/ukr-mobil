<?
class ControllerStartupSeoPro extends Controller {
  private $cache_data = null;

  public function __construct($registry) {
    parent::__construct($registry);
    $this->cache_data = $this->cache->get('seo_pro');
    if (!$this->cache_data) {
      $sql = "SELECT LOWER(keyword) AS keyword, query FROM oc_seo_url ORDER BY seo_url_id";
      $query = $this->db->query($sql);
      $this->cache_data = [];
      foreach ($query->rows as $row) {
        if (isset($this->cache_data['keywords'][$row['keyword']])){
          $this->cache_data['keywords'][$row['query']] = $this->cache_data['keywords'][$row['keyword']];
          continue;
        }
        $this->cache_data['keywords'][$row['keyword']] = $row['query'];
        $this->cache_data['queries'][$row['query']] = $row['keyword'];
      }
      $this->cache->set('seo_pro', $this->cache_data);
    }
  }

  // ------------------------------------------

  private function getFiltersByKeyword($keywords) {
    $keywordList = [];
    foreach ($keywords as $keyword) $keywordList[] = "'{$this->db->escape($keyword)}'";
    $sqlKeywords = implode(',', $keywordList);

    if (empty($sqlKeywords)) return [];

    $sql = "
      SELECT queryKey AS `key`, queryValue AS value, keyword, name
      FROM seo_filter_url
      WHERE keyword IN ({$sqlKeywords})
      ORDER BY ord
    ";
    return $this->db->query($sql)->rows;
  }

  // ------------------------------------------

  private function getFiltersByQuery($data) {
    $queryList = [];
    foreach ($data as $key => $value) $queryList[] = "'{$key}={$value}'";
    $sqlQueries = implode(',', $queryList);

    if (empty($sqlQueries)) return [];

    $sql = "
      SELECT queryKey AS `key`, keyword
      FROM seo_filter_url
      WHERE CONCAT(queryKey, '=', queryValue) IN ({$sqlQueries})
      ORDER BY ord
    ";

    $query = $this->db->query($sql);
    $rows = $query->rows;

    $filters = [];
    foreach ($query->rows as $row) {
      unset($data[$row['key']]);
      $filters[] = $row['keyword'];
    }

    return [
      'filters' => $filters,
      'queries' => $data
    ];
  }

  // ------------------------------------------

  private function getCatagories($categoryId) {
    $sql = "
      SELECT c.category_id, cd.name FROM oc_category_path cp
      LEFT JOIN oc_category c ON c.category_id = cp.path_id
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE cp.category_id = {$this->db->escape($categoryId)} AND cd.language_id = 2 AND c.status = 1
      ORDER BY level DESC;
    ";
    return $this->db->query($sql)->rows;
  }

  // ------------------------------------------

  public function index() {
    // file_put_contents('./catalog/controller/startup/__LOG__.json', "-----------\n" . json_encode($this->request)."\n\n", FILE_APPEND);
    $this->url->addRewrite($this);

    if (!isset($this->request->get['_route_'])) return $this->validate();

    $route = $this->request->get['_route_'];
    unset($this->request->get['_route_']);
    $parts = explode('/', trim(utf8_strtolower($route), '/'));

    $rows = [];

    if (isset($this->cache_data['keywords'][$route])){
      $keyword = $route;
      $parts = [$keyword];
      $rows[] = ['keyword' => $keyword, 'query' => $this->cache_data['keywords'][$keyword]];
    } else {
      foreach ($parts as $keyword) {
        if (isset($this->cache_data['keywords'][$keyword])) {
          $rows[] = ['keyword' => $keyword, 'query' => $this->cache_data['keywords'][$keyword]];
        }
      }
    }

    $queries = [];
    foreach ($rows as $row) $queries[$row['keyword']] = $row['query'];

    $categories = [];
    $filters = [];

    foreach ($parts as $part) {
      if (!isset($queries[$part])) {
        $filters[] = $part;
        continue;
      }

      $url = explode('=', $queries[$part], 2);

      if ($url[0] == 'category_id') {
        $categories[] = $url[1];
      } elseif (count($url) > 1) {
        $this->request->get[$url[0]] = $url[1];
      }
    }

    $controller = $queries[$parts[0]] ?? '';

    if (!empty($categories)) {
      $category = (int)end($categories);
      $this->request->get['path'] = implode('_', $categories);
      $controller = 'product/category';
      $this->request->get['route'] = $controller;
      $this->request->request['category'] = $category;
      $this->request->request['categories'] = $this->getCatagories($category);
    } elseif ($controller == 'product/search') $this->request->get['route'] = $controller;

    if (in_array($controller, ['product/category', 'product/search'])) {
      $this->request->request['filters'] = $this->getFiltersByKeyword($filters);
      foreach ($this->request->request['filters'] as $filter) $this->request->get[$filter['key']] = $filter['value'];
    }

    if (isset($this->request->get['search'])) {
      $this->request->get['search'] = preg_replace('/\s+/', ' ', str_replace('\\', '', trim($this->request->get['search'] ?? '')));
      if (empty($this->request->get['search'])) unset($this->request->get['search']);
    }

    if (!isset($this->request->get['route'])) {
      if (isset($this->request->get['product_id'])) $this->request->get['route'] = 'product/product';
      elseif (isset($this->request->get['news_id'])) $this->request->get['route'] = 'information/news/read';
      elseif (isset($this->cache_data['queries'][$route])) $this->response->redirect($this->cache_data['queries'][$route]);
      elseif (!empty($controller)) $this->request->get['route'] = $controller;
    }

    if (isset($this->request->get['route'])) return new Action($this->request->get['route']);
  }

  // ------------------------------------------

  public function rewrite($link) {
    $url = parse_url($link);
    $host = "{$url['scheme']}://{$url['host']}/";
    parse_str($url['query'], $data);

    $route = $data['route'];
    unset($data['route']);

    switch ($route) {
      case 'product/search':
        $isFilters = true;

      case 'product/category':
        if (isset($data['path'])) {
          $isFilters = true;
          preg_match('/(?:_|)(\d+)$/', $data['path'], $categories);
          $data['path'] = $this->getPathByCategory($categories[1]);
          if (!$data['path']) return $link;
        }
        break;

      case 'information/information/agree':
      case 'product/filter/models':
      case 'checkout/cart/remove':
      case 'common/cart/info':
        return $link;
    }

    $link = "{$host}index.php?route={$route}";

    if (count($data)) $link .= '&' . http_build_query($data);

    $queries = [];

    foreach($data as $key => $value) {
      switch($key) {
        case 'product_id':
        case 'category_id':
        case 'news_id':
          $queries[] = "{$key}={$value}";
          unset($data[$key]);
          break;

        case 'path':
          $categories = explode('_', $value);
          foreach($categories as $category) $queries[] = "category_id={$category}";
          unset($data[$key]);
          break;

        case 'page':
          if ($value == '1') unset($data[$key]);
      }
    }

    if (empty($queries)) $queries[] = $route;

    $rows = [];
    foreach($queries as $query) {
      if(isset($this->cache_data['queries'][$query])) {
        $rows[] = array('query' => $query, 'keyword' => $this->cache_data['queries'][$query]);
      }
    }

    $seo_url = '';
    if (count($rows) == count($queries)) {
      $aliases = [];
      foreach ($rows as $row) $aliases[$row['query']] = $row['keyword'];
      foreach ($queries as $query) $seo_url .= '/' . $aliases[$query];
    }

    if (empty($seo_url)) return $link;
    $seo_url = $host . substr($seo_url, 1);

    // file_put_contents('./catalog/controller/startup/__LOG__.txt', json_encode($data) . "\n{$isCategory}\n\n", FILE_APPEND);
    if (isset($isFilters) && count($data)) {
      $dataQuery = $this->getFiltersByQuery($data);
      file_put_contents('./catalog/controller/startup/__LOG__.txt', json_encode($dataQuery) . "\n\n", FILE_APPEND);

      if (!empty($dataQuery['filters'])) $seo_url .= '/' . implode('/', $dataQuery['filters']);
      $data = $dataQuery['queries'];
    }

    if (count($data)) $seo_url .= '?' . http_build_query($data);

    file_put_contents('./catalog/controller/startup/__LOG__.txt', "{$link}\n{$seo_url}\n\n", FILE_APPEND);

    return $seo_url;
  }

  private function getPathByCategory($category_id) {
    $category_id = (int)$category_id;
    if ($category_id < 1) return false;

    static $path = null;

    if (!isset($path)) {
      $path = $this->cache->get('category.seopath');
      if (!isset($path)) $path = [];
    }

    if (!isset($path[$category_id])) {
      $max_level = 10;

      $sql = "SELECT CONCAT_WS('_'";
      for ($i = $max_level-1; $i >= 0; --$i) {
        $sql .= ",t$i.category_id";
      }
      $sql .= ") AS path FROM " . DB_PREFIX . "category t0";
      for ($i = 1; $i < $max_level; ++$i) {
        $sql .= " LEFT JOIN " . DB_PREFIX . "category t$i ON (t$i.category_id = t" . ($i-1) . ".parent_id)";
      }
      $sql .= " WHERE t0.category_id = '" . $category_id . "'";

      $query = $this->db->query($sql);

      $path[$category_id] = $query->num_rows ? $query->row['path'] : false;

      $this->cache->set('category.seopath', $path);
    }

    return $path[$category_id];
  }

  private function validate() {
    if ($this->request->get['route'] == 'error/not_found') return;
    $uri = str_replace('&amp;', '&', ltrim($this->request->server['REQUEST_URI'], '/'));

    if ($uri =='sitemap.xml') return ($this->request->get['route'] = 'extension/feed/google_sitemap');

    $url = $this->config->get('config_' . ($_SERVER['HTTPS'] ? 'ssl' : 'url')) . $uri;
    $queries = array_filter($this->request->get, function($k) {return $k != 'route';}, ARRAY_FILTER_USE_KEY);
    $seo = $this->url->link($this->request->get['route'], $queries);

    // file_put_contents('./catalog/controller/startup/__LOG__.txt', "{$uri}\n{$url}\n{$seo}\n\n", FILE_APPEND);
    if ($url != $seo) $this->response->redirect($seo);
  }
}
?>
