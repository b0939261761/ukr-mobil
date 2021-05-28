<?

$products = json_decode('[
  {"name": "Xiaomi Redmi 5"},
  {"name": "Oppo A31"}
]', true);

uasort($products, function ($a, $b) { return strcmp($a['name'], $b['name']); });
echo json_encode($products);



