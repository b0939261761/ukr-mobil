<?

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

echo translit('⇩⇩⇩ Вже діють знижки ✅', 'ukr');

