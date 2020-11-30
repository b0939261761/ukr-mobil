<?
require_once(__DIR__ . '/../config.php');
require_once(DIR_SYSTEM . 'startup.php');
require_once(DIR_APPLICATION . 'controller/startup/seo_pro.php');

$registry = new Registry();

$config = new Config();
$config->load('catalog');

function translit($value, $lang = 'rus') {
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

  $ukr = [
    'г' => 'h',
    'ґ' => 'g',
    'є' => 'ie',
    'и' => 'y',
    'і' => 'i',
    'ї' => 'i',
    'х' => 'kh'
  ];

  if ($lang == 'ukr') $converter = array_merge($converter, $ukr);

  $value = mb_strtolower($value);
  $value = str_replace('ый', 'iy', $value);
  $value = strtr($value, $converter);
  $value = preg_replace('/-+/', '-', $value);
  $value = preg_replace('/[^A-Za-z0-9-]+/', ' ', $value);
  $value = str_replace(' ', '_', trim($value));
  return $value;
}

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

$sql = "SELECT id, name FROM brands WHERE id NOT IN
  (SELECT queryValue FROM seo_filter_url WHERE queryKey = 'brand')";

foreach ($db->query($sql)->rows as $brand) {
  $keyword = translit($brand['name']);
  $sql = "INSERT INTO seo_filter_url (queryKey, ord, queryValue, keyword, name) VALUES
    ('brand', -2, {$brand['id']}, '{$keyword}', '{$brand['name']}');";
  try {
    $db->query($sql);
  } catch (Exception $ex) {
    echo "Выброшено исключение: {$ex->getMessage()}\n";
  }
}

// ----------------------------

$sql = "SELECT id, name FROM models WHERE id NOT IN
      (SELECT queryValue FROM seo_filter_url WHERE queryKey = 'model')";

foreach ($db->query($sql)->rows as $model) {
  $keyword = translit($model['name']);
  $sql = "INSERT INTO seo_filter_url (queryKey, ord, queryValue, keyword, name) VALUES
    ('model', -1, {$model['id']}, '{$keyword}', '{$model['name']}');";
  try {
    $db->query($sql);
  } catch (Exception $ex) {
    echo "Выброшено исключение: {$ex->getMessage()}\n";
  }
}

// ----------------------------

$sql = "
  SELECT f.queryKey, f.ord, fv.id, fv.name
  FROM filter_values fv
  LEFT JOIN filters f ON f.id = fv.filter_id
  WHERE fv.id NOT IN
    (SELECT queryValue FROM seo_filter_url WHERE queryKey = f.queryKey)
";
foreach ($db->query($sql)->rows as $filter) {
  $keyword = translit($filter['name']);
  $sql = "INSERT INTO seo_filter_url (queryKey, ord, queryValue, keyword, name) VALUES
    ('{$filter['queryKey']}', {$filter['ord']}, {$filter['id']}, '{$keyword}', '{$filter['name']}');";
  try {
    $db->query($sql);
  } catch (Exception $ex) {
    echo "Выброшено исключение: {$ex->getMessage()}\n";
  }
}

// NEWS ---------------------------------

$sql = "
  SELECT
    CONCAT('news_id=', ep.ep_id) AS query,
    epc.epc_title AS name
  FROM ego_post ep
  LEFT JOIN ego_post_content epc ON epc.epc_post = ep.ep_id
  WHERE LOWER(ep.ep_category) = 'news' AND epc.epc_language = 2
    AND ep.ep_id NOT IN (
      SELECT CAST(REGEXP_SUBSTR(query, '\\\\d{1,}$') AS UNSIGNED) AS id
      FROM oc_seo_url
      WHERE query LIKE 'news_id=%' and language_id = 2
    )
  ORDER by ep.ep_id DESC
";

foreach ($db->query($sql)->rows as $news) {
  $keyword = translit($news['name'], 'ukr');
  $sql = "INSERT INTO oc_seo_url (query, keyword) VALUES ('{$news['query']}', '{$keyword}')";
  $db->query($sql);
  try {
  } catch (Exception $ex) {
    echo "Выброшено исключение: {$ex->getMessage()}\n";
  }
}

