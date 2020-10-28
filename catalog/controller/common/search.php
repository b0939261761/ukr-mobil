<?php

use Ego\Providers\Util;

class ControllerCommonSearch extends \Ego\Controllers\BaseController {

	public function index() {
		$this->load->language('common/search');
		$data['text_search'] = $this->language->get('text_search');
		$data['search'] = $input = str_replace('\\', '', Util::getArrItem($this->request->get, 'search', ''));
		return $this->load->view('common/search', $data);
	}

	public function getSuggestion() {
		$requestData = json_decode(file_get_contents('php://input'), true);
    $input = str_replace('\\', '', Util::getArrItem($requestData, 'input', ''));
		$customerGroupId = $this->customer->getGroupId() ?? 1;

		$words = explode(' ', preg_replace('/\s+/', ' ', $input));

		foreach ($words as $item) {
			$word = $this->db->escape($item);
			$implode[] = strlen($word) < 3
				? "(pd.name LIKE '{$word}%' OR pd.name LIKE '% {$word}%')"
				: "pd.name LIKE '%{$word}%'";
		}

		$where_input = implode(' AND ', $implode);

		$sql = "
			SELECT
				aa.*,
				cd.name AS category_name0,
				IF(aa.image = '',
				COALESCE(
					(SELECT image FROM oc_product_image
						WHERE product_id = aa.product_id ORDER BY sort_order ASC LIMIT 1),
					'placeholder.png'
				), aa.image) AS image
			FROM (
				SELECT * FROM (
					SELECT
						cd.category_id,
						cd.name AS category_name,
						p.product_id,
						pd.name AS product_name,
						p.image,
						0.00 AS price,
						0 AS quantity,
						true as is_owner,
						pop.price_min,
						pop.price_max
					FROM product_owner pd
						LEFT JOIN product_owner_prices pop ON pop.product_owner_id = pd.id
						LEFT JOIN oc_product p ON p.product_id = pd.product_id_default
						LEFT JOIN oc_product_to_category ptc ON ptc.product_id = p.product_id
						LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
						LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
					WHERE cp.level = 1
						AND cd.language_id = 2
						AND pop.customer_group_id = {$customerGroupId}
						AND {$where_input}

					UNION ALL

					SELECT
						cd.category_id,
						cd.name AS category_name,
						pd.product_id,
						pd.name AS product_name,
						p.image,
						COALESCE(pdc.price, p.price) AS price,
						(SELECT SUM(pts.quantity) FROM oc_product_to_stock pts WHERE pts.product_id = p.product_id) +
							p.quantity AS quantity,
						false as is_owner,
						0.00 AS price_min,
						0.00 AS price_max
					FROM oc_product p
						LEFT JOIN oc_product_description pd ON pd.product_id = p.product_id
						LEFT JOIN oc_product_to_category ptc ON ptc.product_id = p.product_id
						LEFT JOIN oc_category_path cp ON cp.category_id = ptc.category_id
						LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
						LEFT JOIN oc_product_discount AS pdc ON pdc.product_id = p.product_id
							AND pdc.customer_group_id = {$customerGroupId}
					WHERE p.status = 1
						AND pd.language_id = 2
						AND cd.language_id = 2
						AND cp.level = 1 AND {$where_input}
					ORDER BY is_owner DESC, quantity DESC
					) aa
				GROUP BY category_name
				ORDER BY is_owner DESC, quantity DESC
			) aa
			LEFT JOIN oc_category_path cp ON cp.category_id = aa.category_id
			LEFT JOIN oc_category_description cd ON cd.category_id = cp.path_id
			WHERE cp.level = 1 AND cd.language_id = 2
			GROUP BY product_id
			ORDER BY is_owner DESC, quantity DESC
			LIMIT 10
		";

		$baseModel = new \Ego\Models\BaseModel();
		$dataQuery = $baseModel->_getDb()->prepare($sql);
		$dataQuery->execute();

		$data = $dataQuery->fetchAll();

		$currency = $this->session->data['currency'];
		$config_theme = $this->config->get('config_theme');
		$image_width = $this->config->get("theme_{$config_theme}_image_product_width");
		$image_height = $this->config->get("theme_{$config_theme}_image_product_height");
		$this->load->model('tool/image');

		foreach ($data as &$item) {
			$categoryPath = "path={$this->pathToRootCategory($item['category_id'])}&search={$input}";

			$item['category_url'] = $this->url->link('product/search', $categoryPath);
			$item['product_url'] = $this->url->link('product/product', "product_id={$item['product_id']}");

			$image = $item['image'] ? $item['image'] : 'placeholder.png';
			$item['product_image'] = $this->model_tool_image->resize($image, $image_width, $image_height);

			if ($item['is_owner']) {
				$item['price_min'] = $item['price_min'] ? $this->currency->format($item['price_min'], $currency): 0;
				$item['price_max'] = $item['price_max'] ? $this->currency->format($item['price_max'], $currency): 0;
			} else {
				$item['product_price'] = $item['price'] ? $this->currency->format($item['price'], $currency) : 0;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode([ 'data' => $data ]));
	}

	private function pathToRootCategory($categoryId) {
		if ($categoryId <= 0) return null;

		$categoryModel = new \Ego\Models\Category();

		while (!empty($parentCategory = $categoryModel->get($categoryId, true))) {
			if ($parentCategory->getCategoryId() <= 0) break;

			$categoryId = $parentCategory->getParentId();
			$path[] = $parentCategory->getCategoryId();
		}

		return implode('_', array_reverse($path));
	}
}
