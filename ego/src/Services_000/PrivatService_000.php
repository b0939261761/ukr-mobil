<?php

namespace Ego\Services;

class PrivatService {

	/**
	 * Return actual currency value
	 *
	 * @param string $currencyCode
	 * @return \SimpleXMLElement
	 */
	public static function getCurrency(string $currencyCode = '') {
		$url = 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=5';

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$page = curl_exec($curl);
		curl_close($curl);
		unset($curl);

		$xml = new \SimpleXMLElement($page);
		return $xml->xpath("/exchangerates/row/exchangerate[@ccy='{$currencyCode}']/@sale")[0]['sale'];
	}

}

