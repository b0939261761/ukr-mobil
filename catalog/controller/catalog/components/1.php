<?
$arr = json_decode('[{"ord": 3, "name": "Цвет", "values": [{"ord": 1, "name": "Black", "color": "#000000", "active": 1, "available": 1, "product_id": 10001490}], "isColor": 1}, {"ord": 1, "name": "Качество", "values": [{"ord": 2, "name": "High Copy", "color": "", "active": 1, "available": 1, "product_id": 10001490}, {"ord": 0, "name": "Original PRC", "color": "", "active": 0, "available": 0, "product_id": 10275}], "isColor": 0}, {"ord": 2, "name": "Тип матрицы", "values": [{"ord": 3, "name": "TFT", "color": "", "active": 1, "available": 1, "product_id": 10001490}, {"ord": 2, "name": "OLED", "color": "", "active": 0, "available": 0, "product_id": 10275}], "isColor": 0}]', true);
uasort($arr, function ($a, $b) { return $a['ord'] - $b['ord']; });

foreach ($arr as &$property) {
  uasort($property['values'], function ($a, $b) { return $a['ord'] - $b['ord']; });
}

echo json_encode($arr);
