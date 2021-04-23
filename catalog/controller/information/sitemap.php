<?
class ControllerInformationSitemap extends Controller {
  public function index() {
    $sitemapId = $this->request->get['sitemap_id'] ?? '';
    $page = (int)($this->request->get['page'] ?? 1);

    $limit = 150;
    $start = ($page - 1) * $limit;
    $end = $start + $limit - 1;

    $activeMenu = $sitemapId;
    if ($sitemapId == 'about') {
      $data['links'] = $this->getAbout();
    } elseif ($sitemapId == 'tracking') {
      $data['links'] = ['link' => [
        'link' => $this->url->link('information/tracking'), 'name' => 'Сервис']
      ];
    } elseif ($sitemapId == 'news') {
      $data['links'] = $this->getNews();
    } elseif ((int)$sitemapId) {
      $data['links'] = $this->getCategories($sitemapId, $start, $end);
      $activeMenu = $data['links']['categoryId0'];
    };

    $data['menu'] = $this->getMenu($activeMenu);

    $totals = $data['links']['totals'] ?? 0;
    if ($totals) {
      $data['pagination'] = $this->getPagination($page, $totals, $limit, $sitemapId);
    }

    $data['headingH1'] = 'Карта сайта';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('information/sitemap', $data));
  }

  private function getCategories($id, $start, $end) {
    $sql = "
      WITH
      tmpCategory AS (
        SELECT
          cd.name,
          GROUP_CONCAT(cp.path_id ORDER BY level SEPARATOR '_') AS path
        FROM oc_category_path cp
        LEFT JOIN oc_category_description cd ON cd.category_id = cp.category_id
        WHERE cp.category_id = {$id}
      ),
      tmpModels AS (
        SELECT
          m.brand_id AS brandId,
          b.name AS brandName,
          b.ord AS brandOrd,
          pm.model_id AS modelId,
          m.name AS modelName,
          m.ord AS modelOrd
        FROM products_models pm
        LEFT JOIN oc_product_to_category ptc ON ptc.product_id = pm.product_id
        LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
        LEFT JOIN models m ON m.id = pm.model_id
        LEFT JOIN brands b ON b.id = m.brand_id
        where cp.path_id = {$id}
        GROUP BY cp.path_id, m.brand_id, pm.model_id
      ),
      tmpBrandsModels AS (
        SELECT DISTINCT
          brandId, brandName, brandOrd, 0 AS modelId, '' AS modelName,
          0 AS modelOrd, brandName AS name
        FROM tmpModels
        UNION ALL
        SELECT
          brandId, brandName, brandOrd, modelId, modelName, modelOrd,
          CONCAT(brandName, ' ', modelName) AS name
        FROM tmpModels
        ORDER BY brandOrd, brandName, modelOrd, modelName
      ),
      tmpBrandsModelsAgg AS (
        SELECT
          IF(COUNT(tmpBrandsModels.brandId),
            JSON_ARRAYAGG(JSON_OBJECT(
              'path', tmpCategory.path,
              'name', tmpBrandsModels.name,
              'brandId', tmpBrandsModels.brandId,
              'modelId', tmpBrandsModels.modelId
            )),
            JSON_ARRAY()) AS filters
        FROM tmpBrandsModels
        LEFT JOIN tmpCategory ON true
      ),
      tmpCategories AS (
        SELECT cd.name, c.category_id AS categoryId
        FROM oc_category c
        LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
        WHERE c.parent_id = {$id}
        ORDER BY c.sort_order, cd.name
      ),
      tmpCategoriesAgg AS (
        SELECT
          IF(COUNT(categoryId),
            JSON_ARRAYAGG(JSON_OBJECT('categoryId', categoryId, 'name', name)),
            JSON_ARRAY()) AS categories
        FROM tmpCategories
      )
      SELECT
        tmpCategory0.categoryId0,
        tmpCategory.path,
        tmpCategory.name,
        tmpCategoriesAgg.categories,
        tmpBrandsModelsAgg.filters
      FROM tmpCategory
      LEFT JOIN tmpCategoriesAgg ON true
      LEFT JOIN tmpBrandsModelsAgg ON true
      LEFT JOIN (
        SELECT path_id AS categoryId0
        FROM oc_category_path
        WHERE category_id = {$id} AND level = 0
      ) AS tmpCategory0 ON true
    ";

    $category = $this->db->query($sql)->row;
    foreach (json_decode($category['categories'], true) as $item) {
      $categories[] = [
        'link' => $this->url->link('information/sitemap', ['sitemap_id' => $item['categoryId']]),
        'name' => $item['name']
      ];
    }

    $totals = -1;
    foreach (json_decode($category['filters'], true) as $item) {
      ++$totals;
      if ($totals >= $start && $totals <= $end) {
        $query = ['path' => $item['path'], 'brand' => $item['brandId']];
        if ($item['modelId']) $query['model'] = $item['modelId'];
        $filters[] = [
          'link' => $this->url->link('product/category', $query),
          'name' => $item['name']
        ];
      }
    }

    $link = [
      'link' => $this->url->link('product/category', ['path' => $category['path']]),
      'name' => $category['name']
    ];

    return [
      'categoryId0' => $category['categoryId0'],
      'link'        => $link,
      'totals'      => $totals,
      'categories'  => $categories ?? [],
      'filters'     => $filters ?? []
    ];
  }

  private function getNews() {
    $sql = "
      SELECT ep.ep_id AS id, epc.epc_title AS name
      FROM ego_post ep
      LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
      WHERE LOWER(ep.ep_category) = 'news'
      ORDER by ep.ep_id DESC
    ";

    foreach ($this->db->query($sql)->rows as $item) {
      $news[] = [
        'link' => $this->url->link('information/news/read', ['news_id' => $item['id']]),
        'name' => $item['name']
      ];
    }

    return [
      'link'     => [
        'link' => $this->url->link('information/news'),
        'name' => 'Новости'
      ],
      'children' => $news ?? []
    ];
  }

  private function getAbout() {
    return [
      'children' => [
        [
          'link' => $this->url->link('information/about_us'),
          'name' => 'О компании'
        ], [
        'link' => "{$this->url->link('information/about_us')}#delivery",
        'name' => 'Доставка'
        ], [
          'link' => "{$this->url->link('information/about_us')}#warrantly",
          'name' => 'Гарантии'
        ], [
          'link' => "{$this->url->link('information/about_us')}#delivery",
          'name' => 'Контакты'
        ]
      ]
    ];
  }

  private function getMenu($sitemapId) {
    $sql = "
      SELECT cd.name, c.category_id as id
      FROM oc_category c
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE parent_id = 0
    ";

    $categories = [];
    foreach($this->db->query($sql)->rows as $item) {
      $categories[] = [
        'link'     => $this->url->link('information/sitemap', ['sitemap_id' => $item['id']]),
        'name'     => $item['name'],
        'isActive' => $sitemapId == $item['id']
      ];
    }

    return array_merge($categories, [
      [
        'link'     => $this->url->link('information/sitemap', ['sitemap_id' => 'about']),
        'name'     => 'О нас',
        'isActive' => $sitemapId == 'about'
      ], [
        'link'     => $this->url->link('information/sitemap', ['sitemap_id' => 'tracking']),
        'name'     => 'Сервис',
        'isActive' => $sitemapId == 'tracking'
      ], [
        'link'     => $this->url->link('information/sitemap', ['sitemap_id' => 'news']),
        'name'     => 'Новости',
        'isActive' => $sitemapId == 'news'
      ]
    ]);
  }

  private function getPagination($page, $total, $limit, $sitemapId) {
    $query = ['page' => '{page}'];
    if ($sitemapId) $query['sitemap_id'] = $sitemapId;
    $url = $this->url->link('information/sitemap', $query);

    $pagination = new Pagination();
    $pagination->page = $page;
    $pagination->total = $total;
    $pagination->limit = $limit;
    $pagination->url = $url;
    return $pagination->render();
  }
}
