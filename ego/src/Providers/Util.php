<?php

namespace Ego\Providers;

//@todo: Unit Tests

/**
 * Class Util
 *
 * Util functions
 *
 * @package Ego\Providers
 */
class Util {

	/**
	 * Convert string to float
	 *
	 * @param $val
	 * @return float
	 */
	public static function strToFloat($val) {
		if (is_string($val)) {
			return floatval(str_replace(',', '.', $val));
		}

		return $val;
	}

	/**
	 * Convert string to bool
	 *
	 * @param $val
	 * @return bool
	 */
	public static function strToBool($val) {
		if (is_bool($val)) {
			return $val;
		}

		$val = trim($val);

		return $val === 'true' || $val === '1';
	}

	/**
	 * Retrieve item from array by path
	 *
	 * @param $arr
	 * @param $path
	 * @param null $default
	 * @return null
	 */
	public static function getArrItem(&$arr, $path, $default = null) {
		if (strpos($path, '.') > 0) {
			$inputItemList = explode('.', $path);
			$tempKey = array_shift($inputItemList);
			$temp = isset($arr[$tempKey]) ? $arr[$tempKey] : $default;

			foreach ($inputItemList as $inputItem) {
				if (isset($temp[$inputItem])) {
					$temp = $temp[$inputItem];
				} else {
					return $default;
				}
			}
		} else {
			$temp = isset($arr[$path]) ? $arr[$path] : $default;
		}

		return $temp;
	}

	/**
	 * Remove Email and Phone from string
	 *
	 * @param $string
	 * @return null|string|string[]
	 */
	public static function removeEmailAndPhoneFromString($string) {
		// remove email
		$string = preg_replace('/([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/', '', $string);

		// remove phone
		//$string = preg_replace('/([0-9]+[\- ]?[0-9]+)/', '', $string);
		//$string = preg_replace('/(\+[0-9]+)/', '', $string);
		$string = preg_replace('/[0-9]/', '', $string);

		return $string;
	}

	/**
	 * Remove dog
	 *
	 * @param $string
	 * @return null|string|string[]
	 */
	public static function removeDog($string) {
		$string = preg_replace('/\@/', '', $string);

		return $string;
	}

	/**
	 * Remove links from string
	 *
	 * @param $string
	 * @return null|string|string[]
	 */
	public static function removeLinkFromString($string) {
		$pattern = "/[a-zA-Z]*[:\/\/]*[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i";
		$replacement = "";

		return preg_replace($pattern, $replacement, $string);
	}
	
	/**
	 * Upload Image from Url
	 *
	 * @param string $imageUrl
	 * @param string $uploadPath
	 * @return bool|int
	 */
	public static function uploadImageFromUrl(string $imageUrl, string $uploadPath) {
		return file_put_contents($uploadPath, file_get_contents($imageUrl));
	}

	/**
	 * Check is date is empty or equal null
	 *
	 * @param string $date
	 * @return bool
	 */
	public static function isEmptyDate(string $date) {
		return empty($date) || strtotime($date) <= 0;
	}
	
}
