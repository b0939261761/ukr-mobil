<?
namespace Cart;
class Main {
  private $db;

  private $domain;
  private $canonical;
  private $linkLogo;
  private $currency;

  public function __construct($registry) {
    $this->db = $registry->get('db');
  }

  public function getDomain() {
    if (!$this->domain) $this->domain = $_SERVER['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER;
    return $this->domain;
  }

  public function getCanonical() {
    if ($this->canonical) return $this->canonical;
    $requestUri = $_SERVER['REQUEST_URI'];
    $uri = str_replace('&amp;', '&', trim($requestUri, '/'));
    return $this->canonical = $this->domain . explode('?', $uri, 2)[0];
  }

  public function getLinkLogo() {
    if (!$this->linkLogo) $this->linkLogo = "{$this->getDomain()}image/logo.png";
    return $this->linkLogo;
  }

  public function getCurrency() {
    if ($this->currency) return $this->currency;
    $sql = "SELECT value FROM oc_currency WHERE currency_id = 980";
    return $this->currency = $this->db->query($sql)->row['value'] ?? 0;
  }
}
