<?
class ControllerStartupSeoPro extends Controller {
  private $cacheData = null;

  public function __construct($registry) {
    parent::__construct($registry);
    $this->cacheData = $this->cache->get('seo_pro');
    if ($this->cacheData) return;
    $sql = "SELECT LOWER(keyword) AS keyword, query FROM oc_seo_url ORDER BY seo_url_id";
    $query = $this->db->query($sql);
    $this->cacheData = [];
    foreach ($query->rows as $row) {
      if (isset($this->cacheData['keywords'][$row['keyword']])){
        $this->cacheData['keywords'][$row['query']] = $this->cacheData['keywords'][$row['keyword']];
        continue;
      }
      $this->cacheData['keywords'][$row['keyword']] = $row['query'];
      $this->cacheData['queries'][$row['query']] = $row['keyword'];
    }
    $this->cache->set('seo_pro', $this->cacheData);
  }

  // ------------------------------------------

  private function getFiltersByKeyword($keywords) {
    $keywordList = [];
    foreach ($keywords as $keyword) $keywordList[] = "'{$this->db->escape($keyword)}'";
    $sqlKeywords = implode(',', $keywordList);

    if (empty($sqlKeywords)) return [];

    $sql = "
      SELECT id, queryKey AS `key`, queryValue AS value, keyword, name
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


    file_put_contents('./catalog/controller/startup/__LOG__.txt', "-----------\n" .$sql. "\n" . json_encode($this->db->query($sql)->rows) ."\n\n", FILE_APPEND);

    $keywordFilters = [];
    foreach ($this->db->query($sql)->rows as $row) {
      unset($data[$row['key']]);
      $keywordFilters[] = $row['keyword'];
    }

    return ['filters' => $keywordFilters, 'queries' => $data];
  }

  // ------------------------------------------

  private function getCatagories($categoryId) {
    $sql = "
      SELECT c.category_id, cd.name FROM oc_category_path cp
      LEFT JOIN oc_category c ON c.category_id = cp.path_id
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE cp.category_id = {$this->db->escape($categoryId)} AND cd.language_id = 2 AND c.status = 1
      ORDER BY level
    ";
    return $this->db->query($sql)->rows;
  }

  // ------------------------------------------

  public function index() {
    // file_put_contents('./catalog/controller/startup/__LOG__.json', "-----------\n" . json_encode($this->request)."\n\n", FILE_APPEND);
    $this->request->request['domain'] = $_SERVER['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER;
    $requestUri = $_SERVER['REQUEST_URI'];
    $uri = str_replace('&amp;', '&', trim($requestUri, '/'));

    $this->request->request['canonical'] = $this->request->request['domain'] . explode('?', $uri, 2)[0];
    $this->request->request['linkLogo'] = "{$this->request->request['domain']}image/logo.png";

    $sql = "
      SELECT
        GROUP_CONCAT(
          COALESCE(sugr.seo_url_actual, u.slug) ORDER BY rowId SEPARATOR '/'
        ) as url
      FROM JSON_TABLE(
        CONCAT('[\"', REPLACE('{$uri}', '/', '\", \"'), '\"]'),
        \"$[*]\"
        COLUMNS(
          rowId FOR ORDINALITY,
          slug VARCHAR(255) PATH \"$\"
        )
      ) AS u
      LEFT JOIN oc_seo_url_generator_redirects sugr on sugr.seo_url_old = u.slug
    ";

    // PostgresSQL
    // select string_agg(coalesce(current, slug), '/' order by s.row) as slug
    // from unnest(string_to_array('/category1-0/category1-1/product-1/', '/')) WITH ORDINALITY as s (slug, row)
    // left join redirect r on r.old = s.slug

    $redirect = $this->db->query($sql)->row['url'] ?? '';
    if ($redirect && $redirect != $uri) return $this->response->redirect("{$this->request->request['domain']}{$redirect}");

    $this->url->addRewrite($this);
    if (!isset($this->request->get['_route_'])) {
      if ($this->request->get['route'] == 'error/not_found') return;

      $url = "{$this->request->request['domain']}{$uri}";
      $queries = array_filter($this->request->get, function($k) {return $k != 'route';}, ARRAY_FILTER_USE_KEY);
      $seo = $this->url->link($this->request->get['route'], $queries);
      if ($url != $seo) $this->response->redirect($seo);
      return;
    }

    $route = $this->request->get['_route_'];
    unset($this->request->get['_route_']);
    $parts = explode('/', trim(utf8_strtolower($route), '/'));
    $rows = [];

    if (isset($this->cacheData['keywords'][$route])){
      $keyword = $route;
      $parts = [$keyword];
      $rows[] = ['keyword' => $keyword, 'query' => $this->cacheData['keywords'][$keyword]];
    } else {
      foreach ($parts as $keyword) {
        if (isset($this->cacheData['keywords'][$keyword])) {
          $rows[] = ['keyword' => $keyword, 'query' => $this->cacheData['keywords'][$keyword]];
        }
      }
    }

    $queries = [];
    foreach ($rows as $row) $queries[$row['keyword']] = $row['query'];

    $categories = [];
    $keywordFilters = [];

    foreach ($parts as $part) {
      if (!isset($queries[$part])) {
        $keywordFilters[] = $part;
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
    } elseif ($controller == 'product/search') {
      $this->request->get['route'] = $controller;
      $this->request->request['category'] = 0;
    }

    if (in_array($controller, ['product/category', 'product/search'])) {
      $keywordList = [];
      foreach ($keywordFilters as $keyword) $keywordList[] = "'{$this->db->escape($keyword)}'";
      $sqlKeywords = implode(',', $keywordList);

      $filters = [];
      if ($sqlKeywords) {
        $sql = "
          SELECT id, queryKey AS `key`, queryValue AS value, keyword, name
          FROM seo_filter_url WHERE keyword IN ({$sqlKeywords}) ORDER BY ord
        ";
        $filters = $this->db->query($sql)->rows;
      }

      if (count($filters) != count($keywordFilters)) return new Action('error/not_found');
      $this->request->request['filters'] = $filters;
      foreach ($filters as $filter) $this->request->get[$filter['key']] = $filter['value'];
    }

    if (isset($this->request->get['search'])) {
      $this->request->get['search'] = preg_replace('/\s+/', ' ',
        str_replace('\\', '', trim($this->request->get['search'] ?? '')));
      if (empty($this->request->get['search'])) unset($this->request->get['search']);
    }

    if (!isset($this->request->get['route'])) {
      if (isset($this->request->get['product_id'])) $this->request->get['route'] = 'product/product';
      elseif (isset($this->request->get['news_id'])) $this->request->get['route'] = 'information/news/read';
      elseif (isset($this->request->get['sitemap_id'])) $this->request->get['route'] = 'information/sitemap';
      elseif (isset($this->request->get['information_id'])) $this->request->get['route'] = 'information/information';
      elseif (isset($this->cacheData['queries'][$route])) $this->response->redirect($this->cacheData['queries'][$route]);
      elseif (!empty($controller)) $this->request->get['route'] = $controller;
    }

    if (isset($this->request->get['route'])) return new Action($this->request->get['route']);
    return new Action('error/not_found');
  }

  // ------------------------------------------

  public function rewrite($link) {
    $url = parse_url($link);
    $host = "{$url['scheme']}://{$url['host']}/";
    parse_str($url['query'], $data);

    $route = $data['route'];
    unset($data['route']);

    if (in_array($route, ['product/category', 'product/search'])) $isFilters = true;
    elseif ($route == 'product/product/review') return $link;
    $link = "{$host}index.php?route={$route}";
    if (count($data)) $link .= '&' . http_build_query($data);

    $queries = [];

    foreach($data as $key => $value) {
      switch($key) {
        case 'product_id':
        case 'category_id':
        case 'news_id':
        case 'sitemap_id':
        case 'information_id':
          $queries[] = "{$key}={$value}";
          unset($data[$key]);
          break;

        case 'path':
          $categories = explode('_', $value);

          if (count($categories) === 3) {
            $lastCategory = (int)array_pop($categories);

            if ($lastCategory) {
              $sql = "SELECT brand_id AS brandId FROM oc_category WHERE category_id = {$lastCategory}";
              $brandId = $this->db->query($sql)->row['brandId'] ?? 0;
              if ($brandId) $data['brand'] = $brandId;
              else $categories[] = $lastCategory;
            }
          }

          foreach($categories as $category) $queries[] = "category_id={$category}";
          unset($data[$key]);
          break;

        case 'page':
          if ($value == '1') unset($data[$key]);
          break;
      }
    }

    if (empty($queries)) $queries[] = $route;

    $rows = [];
    foreach($queries as $query) {
      if(isset($this->cacheData['queries'][$query])) {
        $rows[] = ['query' => $query, 'keyword' => $this->cacheData['queries'][$query]];
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

    if (isset($isFilters) && count($data)) {
      $dataQuery = $this->getFiltersByQuery($data);
      if (!empty($dataQuery['filters'])) $seo_url .= '/' . implode('/', $dataQuery['filters']);
      $data = $dataQuery['queries'];
    }

    if (count($data)) $seo_url .= '?' . http_build_query($data);
    return $seo_url;
  }
}
