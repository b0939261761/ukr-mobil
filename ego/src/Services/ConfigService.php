<?php

namespace Ego\Services;

class ConfigService {

	public function getSiteUrl() {
		return HTTPS_SERVER;
	}

	/**
	 * Return site title
	 *
	 * @return string
	 */
	public function getSiteTitle() {
		return 'UKR Mobil';
	}

	/**
	 * Return email of administrator
	 *
	 * @return mixed
	 */
	public function getEmailAdministrator() {
		return [
			'robot@ukr-mobil.com'
		];
	}

	/**
	 * Return main administrator email
	 *
	 * @return string
	 */
	public function getEmailAdministratorMain() {
		return 'robot@ukr-mobil.com';
	}

}
