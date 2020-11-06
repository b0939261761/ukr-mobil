<?
class ControllerInformationGoogleSitemap extends Controller {
  private function generateSitemap() {
    $sitemap = $sitemapImage = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $sitemapImage .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
    $sitemapImage .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

    $sql = "
      WITH
        tmpProduct AS (
          SELECT
            p.product_id as id,
            p.image,
            IF(count(t.image), JSON_ARRAYAGG(t.image), JSON_ARRAY()) AS images
          FROM oc_product p
          LEFT JOIN LATERAL (
            SELECT image FROM oc_product_image pi
            WHERE pi.product_id = p.product_id AND p.image != pi.image
            ORDER BY pi.sort_order
          ) AS t ON true
          WHERE p.status = 1
          GROUP BY p.product_id
        )
        SELECT id, IF(image, JSON_ARRAY_APPEND(images, '$', image), images) AS images
        FROM tmpProduct
    ";

    foreach ($this->db->query($sql)->rows as $product) {
      $loc = "<loc>{$this->url->link('product/product', ['product_id' => $product['id']])}</loc>";
      $sitemap .= "<url>{$loc}</url>";
      $images = json_decode($product['images'], true);

      if (empty($images)) continue;
      $sitemapImage .= "<url>{$loc}";
      foreach ($images as $image) {
        if (!is_file(DIR_IMAGE . $image)) {
          file_put_contents('./catalog/controller/information/__LOG__.txt', $image);
          continue;
        }

        $urlImage = HTTPS_SERVER . "image/{$image}";
        $sitemapImage .= "<image:image><image:loc>{$urlImage}</image:loc></image:image>";
      }

      $sitemapImage .= "</url>";
    }

    $sql = 'SELECT category_id AS id FROM oc_category WHERE status = 1';
    foreach ($this->db->query($sql)->rows as $category) {
      $sitemap .= "<loc>{$this->url->link('product/category', ['product_id' => $category['id']])}</loc>";
    }

    $sitemap .= '</urlset>';
    file_put_contents('./catalog/controller/information/sitemap.xml', $sitemap);

    $sitemapImage .= '</urlset>';
    file_put_contents('./catalog/controller/information/sitemapimages.xml', $sitemapImage);
  }

  public function index() {
    $this->generateSitemap();
    $this->response->setOutput('');
	}
}
