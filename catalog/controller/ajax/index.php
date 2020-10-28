<?php

use Ego\Controllers\BaseController;
use Ego\Providers\MailProvider;
use Ego\Providers\Util;
use Ego\Providers\Validator;

class ControllerAjaxIndex extends BaseController {

	public function newSendCallback() {
		$success = false;
		$msg = self::MSG_INTERNAL_ERROR;
		$data = [];

		try {
			$this->onlyPost();
			$this->load->language('ajax/index');

			//region Input Data
			$transferData = $this->getInput('transferData');
			//endregion

			//region Check required fields is not empty
			if (($errorField = Validator::isRequiredFieldsEmpty($transferData))) {
				$description = Util::getArrItem($errorField, 'description', '');

				throw new \RuntimeException("Field '{$description}' must be filled.");
			}
			//endregion

			$phoneNumber = Util::getArrItem($transferData, 'callback_phone_number.value');

			//region Define Services
			$configService = new \Ego\Services\ConfigService();
			//endregion

			(new MailProvider())
				->setTo($configService->getEmailAdministrator())
				->setFrom($configService->getEmailAdministratorMain(), $configService->getSiteTitle())
				->setSubject('New callback')
				->setView('mails.callback')
				->setBodyData([
					'header-title' => 'New callback',
					'text' => sprintf($this->language->get('callback_mail_text'), $phoneNumber)
				])
				->sendMail();

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

}
