<?php

namespace Ego\Providers;

//@todo: Unit Tests

/**
 * Class Validator
 *
 * Validator provider with specific functions
 *
 * @package Ego\Providers
 */
class Validator {

	/**
	 * Check required fields with special format of input data
	 *
	 * $formData = [
	 *      [
	 *          'value' => '_some_value_with_variable_format_',
	 *          'required' => true//    boolean value recommend or string format 'true'|'false'
	 *      ]
	 * ]
	 *
	 * @param array $formData
	 * @return bool|mixed
	 */
	public static function isRequiredFieldsEmpty(array $formData) {
		foreach ($formData as $field) {
			$required = is_string($field['required']) ? $field['required'] === 'true' : $field['required'] === true;

			if ($required && empty($field['value']) && $field['value'] !== 0) {
				return $field;
			}
		}

		return false;
	}

	/**
	 * Check required fields from required array list and input array data variable
	 *
	 * @param array $requiredFieldList
	 * @param array $formData
	 * @return bool|array - TRUE    - all required fields in form data exists and are not empty.
	 *                      array   - some of required fields not exists or are empty. Empty fields list
	 */
	public static function checkRequiredFields(array $requiredFieldList, array $formData) {
		$emptyFieldList = [];

		foreach ($requiredFieldList as $fieldName) {
			if (
				!isset($formData[$fieldName]) ||
				(empty($formData[$fieldName]) && $formData[$fieldName] != 0) ||
				(is_string($formData[$fieldName]) && strlen($formData[$fieldName]) === 0)
			) {
				$field = [];

				if (isset($formData[$fieldName])) {
					$field = $formData[$fieldName];
				}

				$emptyFieldList[$fieldName] = $field;
			}
		}

		if (count($emptyFieldList) > 0) {
			return $emptyFieldList;
		}

		return false;
	}

}
