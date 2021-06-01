<?
class ControllerNews extends Controller {
  public function index() {
    $newsId = (int)($this->request->get['news_id'] ?? 0);
    $news = $this->getNews($newsId);
    if (empty($news)) return new Action('404');

    $data['headingH1'] = $news['title'];
    $title = "{$data['headingH1']} - новости от UKRMobil";
    $description = "{$data['headingH1']} ✅ Новости от UKRMobil ✅ Актуально ✅ Полезно";
    $this->document->setTitle($title);
    $this->document->setDescription( $description);

    $breacrumbs = [['name' => 'Новини', 'link' => $this->url->link('news_list')]];
    $this->document->setMicrodataBreadcrumbs($breacrumbs);
    $this->document->setMicrodata($this->getMicrodata($news));

    $this->document->addMeta(['property' => 'og:title', 'content' => $title]);
    $this->document->addMeta(['property' => 'og:description', 'content' => $description]);
    $this->document->addMeta(['property' => 'og:url', 'content' => $news['url']]);
    $this->document->addMeta(['property' => 'og:image', 'content' => $news['image']]);

    $this->document->addCustomStyle('/resourse/styles/news.min.css');
    $this->document->addPreload('/resourse/scripts/news.min.js', 'script');
    $this->document->addCustomScript('/resourse/scripts/news.min.js');

    $data['content'] = $news['content'];
    $data['date'] = $news['date'];
    $data['products'] = $this->getProducts($news);

    $breacrumbsData = ['breadcrumbs' => $breacrumbs];
    $data['breadcrumbs'] = $this->load->view('shared/components/breadcrumbs/breadcrumbs', $breacrumbsData);
    $data['rightMenu'] = $this->load->controller('shared/components/right_menu');
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');
    echo $this->load->view('news/news', $data);
  }

  private function getNews($newsId) {
    if (empty($newsId)) return null;
    $sql = "
      SELECT
        epc.epc_title AS title,
        epc.epc_content AS content,
        ep.productId1,
        ep.productId2,
        ep.productId3,
        ep.productId4,
        ep.productId5,
        COALESCE(ep.ep_preview_image, 'placeholder.jpg') AS image,
        DATE_FORMAT(ep.ep_date_update, '%d.%m.%Y') AS date,
          CONCAT(DATE_FORMAT(ep.ep_date_create, '%Y-%m-%dT%T'),
          DATE_FORMAT(TIMEDIFF(ep.ep_date_create, UTC_TIMESTAMP), '+%H:%i')) AS dateCreate,
        CONCAT(DATE_FORMAT(ep.ep_date_update, '%Y-%m-%dT%T'),
          DATE_FORMAT(TIMEDIFF(ep.ep_date_update, UTC_TIMESTAMP), '+%H:%i')) AS dateUpdate
      FROM ego_post_content epc
      LEFT JOIN ego_post ep ON ep.ep_id = epc.epc_post
      WHERE epc.epc_post = {$newsId}
      LIMIT 1;
    ";
    $news = $this->db->query($sql)->row;
    $news['url'] = $this->url->link('news', ['news_id' => $newsId]);
    $news['image'] = $this->image->resize($news['image']);
    return $news;
  }

  private function getMicrodata($news) {
    $microdata = [
      '@context'      => 'http://schema.org',
      '@type'         => 'NewsArticle',
      'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id'   => $news['url']
      ],
      'headline'      => $news['title'],
      'image'         => [$news['image']],
      'datePublished' => $news['dateCreate'],
      'dateModified'  => $news['dateUpdate'],
      'author' => [
        '@type' => 'Person',
        'name'  => 'UKRMOBIL'
      ],
      'publisher'     => [
        '@type' => 'Organization',
        'name'  => 'UKRMOBIL',
        'logo'  => [
          '@type' => 'ImageObject',
          'url'   => $this->main->getLinkLogo()
        ]
      ],
      'description'   => $news['title']
    ];
    return json_encode($microdata);
  }

  private function getProducts($news) {
    $customerGroupId = $this->customer->getGroupId();

    for ($i = 1; $i <= 5; $i++) {
      if ($news["productId{$i}"]) $products[] = $news["productId{$i}"];
    }

    if (empty($products)) return [];
    $productsSQL = implode(', ', $products);

    $sql = "
      WITH
      tmpProducts AS (
        SELECT
          p.product_id AS id,
          pd.name,
          p.isLatest,
          p.isSalesLeader,
          IF(p.dateExpected = '0000-00-00', '', DATE_FORMAT(p.dateExpected, '%d.%m.%Y')) AS dateExpected,
          IF(p.image = '',
            COALESCE(
              (SELECT image FROM oc_product_image
                WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1),
              'placeholder.jpg'
            ),
            p.image) AS image,
          COALESCE(
            (SELECT price FROM oc_product_discount
              WHERE product_id = p.product_id AND customer_group_id = {$this->customer->getGroupId()}),
            p.price) AS priceOld,
          p.quantity AS quantityStore1,
          p.quantity_store_2 AS quantityStore2,
          pgp.priceMin,
          pgp.priceMax,
          IF(COUNT(prop.name),
            JSON_ARRAYAGG(JSON_OBJECT(
              'ord', prop.ord, 'name', prop.name, 'values', prop.`values`, 'isColor', isColor
            )),
            JSON_ARRAY()
          ) AS properties
        FROM oc_product p
        LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
        LEFT JOIN product_group_prices pgp ON pgp.product_group_id = p.product_group_id
          AND customer_group_id = {$this->customer->getGroupId()}
        LEFT JOIN LATERAL (
          SELECT
            ppr.name, ppr.ord, ppr.name = 'Цвет' AS isColor,
            JSON_ARRAYAGG(JSON_OBJECT(
              'ord', ppv.ord, 'name', ppv.name, 'color', ppv.color,
              'isActive', prpr.product_id_link = p.product_id,
              'id', prpr.product_id_link
              )) AS `values`
          FROM products_properties prpr
          LEFT JOIN product_property_values ppv ON ppv.id = prpr.product_property_value_id
          LEFT JOIN product_properties ppr ON ppr.id = ppv.product_property_id
          WHERE prpr.product_id = p.product_id
          GROUP BY ppr.id
        ) prop ON true
        WHERE p.status = 1 AND p.product_id IN ({$productsSQL})
        GROUP BY p.product_id
      ),
      tmpProductsFull AS (
        SELECT
        *,
        COALESCE(
          (SELECT price
            FROM oc_product_special
            WHERE product_id = p.id
              AND customer_group_id = {$this->customer->getGroupId()}
              AND (date_start = '0000-00-00' OR date_start < NOW())
              AND (date_end = '0000-00-00' OR date_end > NOW())
          ),
          priceOld) AS price
        FROM tmpProducts p
      )
      SELECT
        p.id,
        p.name,
        p.image,
        p.isLatest,
        p.isSalesLeader,
        p.dateExpected,
        p.price != p.priceOld AS isPromotions,
        p.quantityStore1,
        p.quantityStore2,
        p.quantityStore1 + p.quantityStore2 AS quantity,
        p.price AS priceUSD,
        ROUND(p.price * c.value) AS priceUAH,
        ROUND(p.priceOld * c.value) AS priceOldUAH,
        p.priceMin AS priceMinUSD,
        p.priceMax AS priceMaxUSD,
        ROUND(p.priceMin * c.value) AS priceMinUAH,
        ROUND(p.priceMax * c.value) AS priceMaxUAH,
        p.properties
      FROM tmpProductsFull p
      LEFT JOIN oc_currency c ON c.currency_id = 980
    ";

    $items = $this->db->query($sql)->rows;

    foreach ($items as &$item) {
      $item['link'] = $this->url->link('product/product', ['product_id' => $item['id']]);
      $item['image'] = $this->image->resize($item['image'], 306, 306);

      $item['properties'] = json_decode($item['properties'], true);
      uasort($item['properties'], function ($a, $b) { return $a['ord'] - $b['ord']; });

      foreach ($item['properties'] as &$property) {
        uasort($property['values'], function ($a, $b) { return $a['ord'] - $b['ord']; });

        foreach ($property['values'] as &$value) {
          if (!$value['isActive']) $value['link'] = $this->url->link('product/product', ['product_id' => $value['id']]);
        }
      }
    }

    return $items;
  }
}

