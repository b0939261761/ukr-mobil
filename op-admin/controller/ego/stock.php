<?php

use Ego\Controllers\BaseController;
use Ego\Providers\Util;
use Ego\Providers\Validator;

class ControllerEgoStock extends BaseController {

	/**
	 * Table list
	 *
	 * @throws Exception
	 */
	public function index() {
		$this->document->setTitle('Stock');

		$data['user_token'] = $this->session->data['user_token'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Stock',
			'href' => $this->url->link('ego/stock', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['add'] = $this->url->link('ego/stock/card', 'user_token=' . $this->session->data['user_token'], true);
		$data['delete'] = html_entity_decode($this->url->link('ego/stock/delete', 'user_token=' . $this->session->data['user_token'], true));

		//region Define Models
		$stocksModel = new \Ego\Models\Stocks();
		//endregion

		//region Prepare Data
		//region Stock list
		$data['stockList'] = [];
		$stockList = $stocksModel->getList();
		$stockList = empty($stockList) ? [] : $stockList;

		foreach ($stockList as &$item) {
			$item['url'] = $this->url->link('ego/stock/card', 'user_token=' . $this->session->data['user_token'] . '&card_id=' . $item['stock_id'], true);
		}

		$data['stockList'] = $stockList;
		//endregion
		//endregion

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		// Run currency update
		if ($this->config->get('config_currency_auto')) {
			$this->load->model('localisation/currency');

			$this->model_localisation_currency->refresh();
		}

		$this->response->setOutput($this->load->view('ego/stock_table', $data));
	}

	/**
	 * Table list
	 *
	 * @throws Exception
	 */
	public function card() {
		$this->document->setTitle('Stock');

		$data['user_token'] = $this->session->data['user_token'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Stock',
			'href' => $this->url->link('ego/stock', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['save'] = html_entity_decode($this->url->link('ego/stock/save', 'user_token=' . $this->session->data['user_token'], true));

		//region Define Models
		$stocksModel = new \Ego\Models\Stocks();
		//endregion

		//region Prepare Data
		$cardId = (int)Util::getArrItem($_GET, 'card_id');

		$data['card'] = $stocksModel->get($cardId);
		//endregion

		//	Set languages
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		//endregion

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		// Run currency update
		if ($this->config->get('config_currency_auto')) {
			$this->load->model('localisation/currency');

			$this->model_localisation_currency->refresh();
		}

		$this->response->setOutput($this->load->view('ego/stock_card', $data));
	}

	/**
	 * Delete row
	 */
	public function delete() {
		$success = false;
		$msg = self::MSG_INTERNAL_ERROR;
		$data = [];

		try {
			$this->onlyPost();

			//region Input Data
			$transferData = $this->getInput('transferData');
			//endregion

			//region Check required fields is not empty
			if (($errorField = Validator::checkRequiredFields([
				'cardId'
			], $transferData))) {
				$description = Util::getArrItem($errorField, 'description', '');

				throw new \RuntimeException("Field '{$description}' must be filled.");
			}
			//endregion

			$cardId = (int)Util::getArrItem($transferData, 'cardId');

			//region Define Models
			$stocksModel = new \Ego\Models\Stocks();
			//endregion

			if (!$stocksModel->delete($cardId)) {
				throw new \RuntimeException("Error occurred while delete record.");
			}

			$success = true;
			$msg = self::MSG_SUCCESS;
		} catch (\Exception $ex) {
			$msg = $ex->getMessage();
		}

		$this->_prepareJson([
			'success' => $success,
			'msg' => $msg,
			'data' => $data
		]);
	}

	/**
	 * Delete row
	 */
	public function save() {
		$success = false;
		$msg = self::MSG_INTERNAL_ERROR;
		$data = [];
		$baseModel = new \Ego\Models\BaseModel();

		try {
			$this->onlyPost();

			$baseModel->_getDb()->beginTransaction();

			//region Input Data
			$cardId = (int)$this->getInput('cardId');
			$transferData = $this->getInput('transferData');
			//endregion

			$name = Util::getArrItem($transferData, 'name', '');
			$address = Util::getArrItem($transferData, 'address', '');

			//region Define Models
			$stocksModel = new \Ego\Models\Stocks();
			//endregion

			$row = (new \Ego\Struct\StockRowStruct())
				->setStockId($cardId)
				->setName($name)
				->setAddress($address);

			//	Update
			if ($cardId > 0) {
				if (!$stocksModel->update($row)) {
					throw new \RuntimeException("Error occurred while update stock.");
				}
			}
			//	Create
			else {
				if (!($cardId = $stocksModel->add($row))) {
					throw new \Exception("Error occurred while create stock.");
				}

				$data['cardId'] = $cardId;
			}

			$baseModel->_getDb()->commit();

			$success = true;
			$msg = self::MSG_SUCCESS;
		} catch (\Exception $ex) {
			$baseModel->_getDb()->rollBack();

			$msg = $ex->getMessage();
		}

		$this->_prepareJson([
			'success' => $success,
			'msg' => $msg,
			'data' => $data
		]);
	}

}
