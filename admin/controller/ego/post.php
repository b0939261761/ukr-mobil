<?php

use Ego\Controllers\BaseController;
use Ego\Providers\Util;
use Ego\Providers\Validator;

class ControllerEgoPost extends BaseController {

	/**
	 * Table list
	 *
	 * @throws Exception
	 */
	public function index() {
		$this->document->setTitle('Posts');

		$data['user_token'] = $this->session->data['user_token'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Posts',
			'href' => $this->url->link('ego/post', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['add'] = $this->url->link('ego/post/card', 'user_token=' . $this->session->data['user_token'], true);
		$data['delete'] = html_entity_decode($this->url->link('ego/post/delete', 'user_token=' . $this->session->data['user_token'], true));

		//region Define Models
		$postModel = new \Ego\Models\EgoPost();
		$postContentModel = new \Ego\Models\EgoPostContent();
		//endregion

		//region Prepare Data
		//region Post List
		$data['postList'] = [];
		$postList = $postModel->getList(true);
		$postList = empty($postList) ? [] : $postList;

		foreach ($postList as $post) {
			$postContentList = $postContentModel->getByPost($post->getId(), null, true, true);
			$postContentList = empty($postContentList) ? [] : $postContentList;

			foreach ($postContentList as $postContent) {
				$data['postList'][] = [
					'id' => $postContent->getPost(),
					'title' => $postContent->getTitle(),
					'category' => $post->getCategory(),
					'url' => $this->url->link('ego/post/card', 'user_token=' . $this->session->data['user_token'] . '&card_id=' . $postContent->getPost(), true)
				];
			}
		}
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

		$this->response->setOutput($this->load->view('ego/post_table', $data));
	}

	/**
	 * Table list
	 *
	 * @throws Exception
	 */
	public function card() {
		$this->document->setTitle('Posts');

		$data['user_token'] = $this->session->data['user_token'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Posts',
			'href' => $this->url->link('ego/post', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['save'] = html_entity_decode($this->url->link('ego/post/save', 'user_token=' . $this->session->data['user_token'], true));

		//region Define Models
		$postModel = new \Ego\Models\EgoPost();
		$postContentModel = new \Ego\Models\EgoPostContent();
		//endregion

		//region Prepare Data
		$cardId = (int)Util::getArrItem($_GET, 'card_id');
		$post = $postModel->get($cardId, true);

		//	Set card data
		$data['card'] = [
			'post' => empty($post) ? [] : $post->toArray(),
			'content' => []
		];

		//region Init Image
		$this->load->model('tool/image');
		$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		//endregion

		//	Preview image
		if (!empty($post) && is_file(DIR_IMAGE . $post->getPreviewImage())) {
			$data['card']['post']['preview_image_thumb'] = $this->model_tool_image->resize($post->getPreviewImage(), 100, 100);
		}

		$postContentList = $postContentModel->getByPost($cardId, null, false, true);
		$postContentList = empty($postContentList) ? [] : $postContentList;

		foreach ($postContentList as $postContent) {
			$data['card']['content'][$postContent->getLanguage()] = $postContent->toArray();
		}

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

		$this->response->setOutput($this->load->view('ego/post_card', $data));
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
			$postModel = new \Ego\Models\EgoPost();
			//endregion

			$postModel->delete($cardId);

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
			$transferData = $this->getInput('transferData');
			//endregion

			$post = Util::getArrItem($transferData, 'post');
			$content = Util::getArrItem($transferData, 'content');
			$cardId = (int)Util::getArrItem($post, 'id');

			//region Define Models
			$postModel = new \Ego\Models\EgoPost();
			$postContentModel = new \Ego\Models\EgoPostContent();
			//endregion

			$postRow = (new \Ego\Struct\EgoPostRowStruct())
				->setId($cardId)
				->setCategory(Util::getArrItem($post, 'category', ''))
				->setPreviewImage(Util::getArrItem($post, 'preview_image', ''));
			/** @var \Ego\Struct\EgoPostContentRowStruct[] $postContentList */
			$postContentList = [];

			foreach ($content as $languageId => $contentItem) {
				$languageId = (int)$languageId;

				$postContentList[] = (new \Ego\Struct\EgoPostContentRowStruct())
					->setPost($postRow->getId())
					->setLanguageId($languageId)
					->setTitle(Util::getArrItem($contentItem, 'title.value'))
					->setContent(Util::getArrItem($contentItem, 'content.value'));
			}

			//	Update
			if ($cardId > 0) {
				if (!$postModel->update($postRow)) {
					throw new \Exception("Error occurred while update post.");
				}

				foreach ($postContentList as $contentItem) {
					if ($postContentModel->isExists($postRow->getId(), $contentItem->getLanguage())) {
						if (!$postContentModel->update($contentItem)) {
							throw new \Exception("Error occurred while update post content.");
						}
					} else {
						if (!$postContentModel->add($contentItem)) {
							throw new \Exception("Error occurred while create post content.");
						}
					}
				}
			} //	Create
			else {
				if (!($cardId = $postModel->add($postRow))) {
					throw new \Exception("Error occurred while create post.");
				}

				$postRow->setId($cardId);

				foreach ($postContentList as $contentItem) {
					$contentItem->setPost($postRow->getId());

					if (!$postContentModel->add($contentItem)) {
						throw new \Exception("Error occurred while create post content.");
					}
				}

				$data['cardId'] = $postRow->getId();
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
