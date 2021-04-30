<?
namespace Cart;
class Category {
  private $currencies = array();
  private $currentCategories;
  private $categoryId;

  public function __construct($registry) {
    $this->db = $registry->get('db');
  }

  public function setCategoryId($categoryId) {
    $this->categoryId = $categoryId;
  }

  public function getCurrentCatagories() {
    if (!$this->currentCategories) {
      $sql = "
        SELECT c.category_id AS id, cd.name FROM oc_category_path cp
        LEFT JOIN oc_category c ON c.category_id = cp.path_id
        LEFT JOIN oc_category_description cd ON cd.category_id = c.category_id
        WHERE cp.category_id = {$this->db->escape($this->categoryId)} AND c.status = 1
        ORDER BY level
      ";
      $this->currentCategories = $this->db->query($sql)->rows;
    }

    return $this->currentCategories;
  }





  // public function format($number, $currency = 'UAH,USD', $value = '', $format = true) {
  //   $string = '';
  //   $currencyValue = $value;
  //   $first = true;

  //   foreach (explode(',', $currency) as $currency) {
  //     $symbol_left = $this->currencies[$currency]['symbol_left'];
  //     $symbol_right = $this->currencies[$currency]['symbol_right'];
  //     $decimal_place = $this->currencies[$currency]['decimal_place'];

  //     if (!$value) {
  //       $currencyValue = $this->currencies[$currency]['value'];
  //     }

  //     $amount = $currencyValue ? (float)$number * $currencyValue : (float)$number;
  //     $amount = round($amount, (int)$decimal_place);

  //     if (!$format) {
  //       return $amount;
  //     }

  //     //$string .= ' ';

  //     if ($symbol_left) {
  //       $string .= $symbol_left;
  //     }

  //     $string .= number_format($amount, (int)$decimal_place, '.', '');

  //     if ($symbol_right) {
  //       $string .= $symbol_right;
  //     }

  //     if ($first) {
  //       $string .= ' (';
  //     }

  //     $first = false;
  //   }

  //   $string .= ')';

  //   return $string;
  // }

  // public function convert($value, $from, $to) {
  //   if (isset($this->currencies[$from])) {
  //     $from = $this->currencies[$from]['value'];
  //   } else {
  //     $from = 1;
  //   }

  //   if (isset($this->currencies[$to])) {
  //     $to = $this->currencies[$to]['value'];
  //   } else {
  //     $to = 1;
  //   }

  //   return $value * ($to / $from);
  // }

  // public function getId($currency) {
  //   if (isset($this->currencies[$currency])) {
  //     return $this->currencies[$currency]['currency_id'];
  //   } else {
  //     return 0;
  //   }
  // }

  // public function getSymbolLeft($currency) {
  //   if (isset($this->currencies[$currency])) {
  //     return $this->currencies[$currency]['symbol_left'];
  //   } else {
  //     return '';
  //   }
  // }

  // public function getSymbolRight($currency) {
  //   if (isset($this->currencies[$currency])) {
  //     return $this->currencies[$currency]['symbol_right'];
  //   } else {
  //     return '';
  //   }
  // }

  // public function getDecimalPlace($currency) {
  //   if (isset($this->currencies[$currency])) {
  //     return $this->currencies[$currency]['decimal_place'];
  //   } else {
  //     return 0;
  //   }
  // }

  // public function getValue($currency) {
  //   if (isset($this->currencies[$currency])) {
  //     return $this->currencies[$currency]['value'];
  //   } else {
  //     return 0;
  //   }
  // }

  public function getId() {
    return 1;
  }
}
