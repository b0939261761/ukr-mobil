<?
class ControllerSitemap extends Controller {
  public function index() {
    $sitemapId = $this->request->get['sitemap_id'] ?? '';
    $page = (int)($this->request->get['page'] ?? 1);

    $this->document->addCustomStyle('/resourse/styles/sitemap.min.css');

    $data['headingH1'] = 'Мапа сайту';
    $this->document->setTitle("{$data['headingH1']} - интернет-магазин UKRMobil");
    $this->document->setDescription("{$data['headingH1']} ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине");
    $this->document->setMicrodataBreadcrumbs();

    $activeMenu = $sitemapId;
    if ($sitemapId == 'about') {
      $data['mainLink'] = [
        'link' => $this->url->link('information/about_us'),
        'name' => 'Про нас'
      ];

    } elseif ($sitemapId == 'tracking') {
      $data['mainLink'] = [
        'link' => $this->url->link('tracking'),
        'name' => 'Сервіс'
      ];
    } elseif ($sitemapId == 'delivery') {
      $data['mainLink'] = [
        'link' => "{$this->url->link('information/about_us')}#delivery",
        'name' => 'Доставка і оплата'
      ];
    } elseif ($sitemapId == 'income') {
      $data['mainLink'] = [
        'link' => $this->url->link('income/income'),
        'name' => 'Надходження'
      ];
    } elseif ($sitemapId == 'contacts') {
      $data['mainLink'] = [
        'link' => "{$this->url->link('information/about_us')}#contact",
        'name' => 'Контакти'
      ];
    } elseif ((int)$sitemapId) {
      $limit = 150;
      $start = ($page - 1) * $limit;
      $end = $start + $limit - 1;

      $data['links'] = $this->getCategories($sitemapId, $start, $end);

      $activeMenu = $data['links']['categoryId0'];
      $data['mainLink'] = $data['links']['mainLink'];

      $total = $data['links']['total'];

      if ($total) {
        $padinationData = [
          'page'     => $page,
          'total'    => $total,
          'limit'    => $limit,
          'routeUrl' => 'sitemap/sitemap',
          'queryUrl' => ['sitemap_id' => $sitemapId]
        ];
        $data['pagination'] = $this->load->controller('shared/components/pagination', $padinationData);
      }
    };

    $breacrumbsData = ['breadcrumbs' => [['name' => $data['headingH1']]]];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);
    $data['menu'] = $this->getMenu($activeMenu);
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('sitemap/sitemap', $data);
  }

  private function getCategories($id, $start, $end) {
    // TODO Filters
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
        WHERE cp.path_id = {$id}
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

    $categoriesTmp = json_decode($category['categories'], true);
    // uasort($categoriesTmp, function ($a, $b) { return strnatcmp($a['name'], $b['name']); });

    foreach ($categoriesTmp as $item) {
      $categories[] = [
        'link' => $this->url->link('sitemap/sitemap', ['sitemap_id' => $item['categoryId']]),
        'name' => $item['name']
      ];
    }

    $total = -1;

    $filtersTmp = json_decode($category['filters'], true);
    // uasort($filtersTmp, function ($a, $b) { return strnatcmp($a['name'], $b['name']); });

    foreach ($filtersTmp as $item) {
      ++$total;
      if ($total >= $start && $total <= $end) {
        $query = ['path' => $item['path'], 'brand' => $item['brandId']];
        if ($item['modelId']) $query['model'] = $item['modelId'];
        $filters[] = [
          'link' => $this->url->link('product/category', $query),
          'name' => $item['name']
        ];
      }
    }

    $mainLink = [
      'link' => $this->url->link('product/category', ['path' => $category['path']]),
      'name' => $category['name']
    ];

    return [
      'categoryId0' => $category['categoryId0'],
      'mainLink'    => $mainLink,
      'total'       => $total,
      'categories'  => $categories ?? [],
      'filters'     => $filters ?? []
    ];
  }

  private function getMenu($sitemapId) {
    $sql = "
      SELECT cd.name, c.category_id AS id
      FROM oc_category c
      LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
      WHERE parent_id = 0
      ORDER BY c.sort_order, cd.name
    ";

    $categories = [];
    foreach($this->db->query($sql)->rows as $item) {
      $categories[] = [
        'link'     => $this->url->link('sitemap/sitemap', ['sitemap_id' => $item['id']]),
        'name'     => $item['name'],
        'isActive' => $sitemapId == $item['id']
      ];
    }

    return array_merge($categories, [
      [
        'link'     => $this->url->link('sitemap/sitemap', ['sitemap_id' => 'about']),
        'name'     => 'Про нас',
        'isActive' => $sitemapId == 'about'
      ], [
        'link'     => $this->url->link('sitemap/sitemap', ['sitemap_id' => 'tracking']),
        'name'     => 'Сервіс',
        'isActive' => $sitemapId == 'tracking'
      ], [
        'link'     => $this->url->link('sitemap/sitemap', ['sitemap_id' => 'delivery']),
        'name'     => 'Доставка і оплата',
        'isActive' => $sitemapId == 'delivery'
      ], [
        'link'     => $this->url->link('sitemap/sitemap', ['sitemap_id' => 'income']),
        'name'     => 'Надходження',
        'isActive' => $sitemapId == 'income'
      ], [
        'link'     => $this->url->link('sitemap/sitemap', ['sitemap_id' => 'contacts']),
        'name'     => 'Контакти',
        'isActive' => $sitemapId == 'contacts'
      ]
    ]);
  }
}
