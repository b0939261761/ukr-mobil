<?php

namespace Ego\Controllers;

use Cart\Cart;
use Cart\Currency;
use Cart\Customer;
use Cart\User;
use Config;
use Controller;
use Document;
use Language;
use Request;
use Response;
use Session;
use Url;

/**
 * Class BaseController
 *
 * @property Request $request
 * @property Cart $cart
 * @property Config $config
 * @property Currency $currency
 * @property Session $session
 * @property Customer $customer
 * @property Document $document
 * @property Language $language
 * @property Response $response
 * @property Url $url
 * @property User $user
 *
 * @package Ego\Controllers
 */
class BaseController extends Controller {

	const MSG_SUCCESS = 'Success';
	const MSG_INTERNAL_ERROR = 'Internal Server Error 500';
	const MSG_SOME_REQUIRED_FIELDS_EMPTY = 'Some of fields area empty.';

	public function __construct($registry) {
		parent::__construct($registry);
	}

	/**
	 * Allow only `POST` request
	 */
	protected function onlyPost() {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('HTTP/1.1 403 Forbidden');

			die();
		}
	}

	/**
	 * Return Input Request data
	 *
	 * @param {String} $inputName
	 * @return null
	 */
	protected function getInput($inputName) {
		$inputData = $GLOBALS['_' . $_SERVER['REQUEST_METHOD']];

		if (strpos($inputName, '.') > 0) {
			$inputItemList = explode('.', $inputName);
			$tempKey = array_shift($inputItemList);
			$temp = isset($inputData[$tempKey]) ? $inputData[$tempKey] : null;

			foreach ($inputItemList as $inputItem) {
				if (isset($temp[$inputItem])) {
					$temp = $temp[$inputItem];
				} else {
					return null;
				}
			}
		} else {
			$temp = isset($inputData[$inputName]) ? $inputData[$inputName] : null;
		}

		return $temp;
	}

	/**
	 * Prepare data for JSON response
	 *
	 * @param array $json Assoc array [success, msg, data];
	 * @param bool $autoResponse
	 * @return mixed|string
	 */
	protected function _prepareJson(array $json, $autoResponse = true) {
		$json = [
			'success' => empty($json['success']) ? false : $json['success'],
			'code' => isset($json['code']) ? $json['code'] : 500,
			'message' => empty($json['msg']) ? 'Неизвестная ошибка' : $json['msg'],
			'data' => empty($json['data']) ? null : $json['data'],
		];

		if ($autoResponse) {
			echo json_encode($json);

			die();
		} else {
			return json_encode($json);
		}
	}


}
