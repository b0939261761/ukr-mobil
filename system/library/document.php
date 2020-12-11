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
    return $this->microdata;
  }

  public function setMicrodata($microdata) {
    $this->microdata[] = $microdata;
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
