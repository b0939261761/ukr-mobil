<?php
class Document {
  private $title;
  private $description;
  private $keywords;
  private $microdata = [];
  private $dataLayer;
  private $links = [];
  private $styles = [];
  private $scripts = [];
  private $metaList = [];

  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }

  public function setDescription($description) {
    $this->description = $description;
  }

  public function getDescription() {
    return $this->description;
  }

  public function setKeywords($keywords) {
    $this->keywords = $keywords;
  }

  public function getKeywords() {
    return $this->keywords;
  }

  public function getMicrodata() {
    $domain = $_SERVER['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER;

    $microdata[] = json_encode([
      '@context'  => 'http://schema.org',
      '@type'     => 'Organization',
      'url'       => $domain,
      'name'      => 'UKRMOBIL',
      'logo'      => "{$domain}image/catalog/logo.png",
      'sameAs'    => [
        'https://www.instagram.com/ukrmobil_cv/',
        'https://t.me/ukrmobil',
        'https://www.facebook.com/Ukrmobil1/'
      ],
      'address'   => [
        '@type'           => 'PostalAddress',
        'addressLocality' => 'г. Черновцы, Украина',
        'streetAddress'   => 'ул. Калиновская, 13А'
      ],
      'telephone' => [
        '+38 093 765 1080',
        '+38 068 765 1080',
        '+38 050 274 2790',
        '+38 095 765 1080'
      ],
      'email'     => 'ukrmobil1@gmail.com'
    ]);

    return array_merge($microdata, $this->microdata);
  }

  public function setMicrodata($microdata) {
    $this->microdata[] = $microdata;
  }

  public function setMicrodataBreadcrumbs($breadcrumbs = []) {
    $breadcrumbsMicrodata[] = [
      '@type'    => 'ListItem',
      'position' => 1,
      'item'     => [
        '@id'  => $_SERVER['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER,
        'name' => 'Главная'
      ]
    ];

    foreach($breadcrumbs as $key=>$value) {
      if (isset($value['link'])) {
        $breadcrumbsMicrodata[] = [
          '@type'    => 'ListItem',
          'position' => $key + 2,
          'item'     => [
            '@id'  => $value['link'],
            'name' => $value['name']
          ]
        ];
      }
    };

    $microdata = [
      '@context'        => 'https://schema.org/',
      '@type'           => 'BreadcrumbList',
      'itemListElement' => $breadcrumbsMicrodata
    ];

    $this->microdata[] = json_encode($microdata);
  }

  public function getDataLayer() {
    return $this->dataLayer;
  }

  public function setDataLayer($dataLayer) {
    $this->dataLayer = $dataLayer;
  }

  public function addLink($href, $rel) {
    $this->links[$href] = [ 'href' => $href, 'rel' => $rel ];
  }

  public function getLinks() {
    return $this->links;
  }

  public function addStyle($href, $rel = 'stylesheet', $media = 'screen') {
    $this->styles[$href] = [
      'href'  => $href,
      'rel'   => $rel,
      'media' => $media
    ];
  }

  public function getStyles() {
    return $this->styles;
  }

  public function addScript($href, $postion = 'header') {
    $this->scripts[$postion][$href] = $href;
  }

  public function getScripts($postion = 'header') {
    return $this->scripts[$postion] ?? [];
  }

  public function addMeta($payload) {
    $this->metaList[] = $payload;
  }

  public function getMetaList() {
    return $this->metaList;
  }
}
