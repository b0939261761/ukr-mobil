<?
require_once(__DIR__ . '/../config.php');
require_once(DIR_SYSTEM . 'startup.php');

new GenerateSeoFilters();

class GenerateSeoFilters {
  private $db;

  public function __construct() {
    $this->db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    $this->db->query('DELETE FROM seo_filter_url');
    $this->setBrands();
    $this->setModels();
    $this->setFilters();
    $this->setStocks();
    $this->db = null; // Закрываем соединение к DB
  }

  private function translit($value) {
    $converter = [
      'а' => 'a',
      'б' => 'b',
      'в' => 'v',
      'г' => 'g',
      'д' => 'd',
      'е' => 'e',
      'ё' => 'e',
      'ж' => 'zh',
      'з' => 'z',
      'и' => 'i',
      'й' => 'y',
      'к' => 'k',
      'л' => 'l',
      'м' => 'm',
      'н' => 'n',
      'о' => 'o',
      'п' => 'p',
      'р' => 'r',
      'с' => 's',
      'т' => 't',
      'у' => 'u',
      'ф' => 'f',
      'х' => 'h',
      'ц' => 'ts',
      'ч' => 'ch',
      'ш' => 'sh',
      'щ' => 'shch',
      'ь' => '',
      'ы' => 'y',
      'ъ' => '',
      'э' => 'e',
      'ю' => 'yu',
      'я' => 'ya',
    ];

    $value = mb_strtolower($value);
    $value = str_replace('ый', 'iy', $value);
    $value = str_replace(' ', '_', $value);
    $value = strtr($value, $converter);
    $value = preg_replace('/-+/', '-', $value);
    return $value;
  }

  private function setStocks() {
    foreach ($this->db->query('SELECT id, name FROM store')->rows as $stock) {
      $keyword = $this->translit($stock['name']);
      $sql = "INSERT INTO seo_filter_url (queryKey, ord, queryValue, keyword, name) values
        ('stock', 999999, {$stock['id']}, '{$keyword}', '{$stock['name']}');";
      try {
        $this->db->query($sql);
      } catch (Exception $e) {
        echo "Выброшено исключение: {$ex->getMessage()}\n";
      }
    }
  }

  private function setBrands() {
    foreach ($this->db->query('SELECT id, name FROM brands')->rows as $brand) {
      $keyword = $this->translit($brand['name']);
      $sql = "INSERT INTO seo_filter_url (queryKey, ord, queryValue, keyword, name) VALUES
        ('brand', -2, {$brand['id']}, '{$keyword}', '{$brand['name']}');";
      try {
        $this->db->query($sql);
      } catch (Exception $ex) {
        echo "Выброшено исключение: {$ex->getMessage()}\n";
      }
    }
  }

  private function setModels() {
    foreach ($this->db->query('SELECT id, name FROM models')->rows as $model) {
      $keyword = $this->translit($model['name']);
      $sql = "INSERT INTO seo_filter_url (queryKey, ord, queryValue, keyword, name) VALUES
        ('model', -1, {$model['id']}, '{$keyword}', '{$model['name']}');";
      try {
        $this->db->query($sql);
      } catch (Exception $ex) {
        echo "Выброшено исключение: {$ex->getMessage()}\n";
      }
    }
  }

  private function setFilters() {
    $sql = "
      SELECT f.queryKey, f.ord, fv.id, fv.name
      FROM filter_values fv
      LEFT JOIN filters f ON f.id = fv.filter_id;
    ";
    foreach ($this->db->query($sql)->rows as $filter) {
      $keyword = $this->translit($filter['name']);
      $sql = "INSERT INTO seo_filter_url (queryKey, ord, queryValue, keyword, name) VALUES
        ('{$filter['queryKey']}', {$filter['ord']}, {$filter['id']}, '{$keyword}', '{$filter['name']}');";
      try {
        $this->db->query($sql);
      } catch (Exception $ex) {
        echo "Выброшено исключение: {$ex->getMessage()}\n";
      }
    }
  }
}
