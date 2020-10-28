<?php

namespace Ego\Struct;

abstract class BaseStruct {

	/**
	 * Convert current object to array
	 *
	 * @param boolean $cameCase - key in camelcase format or divided by underscore
	 * @return array
	 */
	public function toArray($cameCase = false) {
		$methodList = get_class_methods(static::class);
		$result = [];

		foreach ($methodList as $method) {
			//	Select only get methods
			if (strpos($method, 'get') !== 0) {
				continue;
			}

			$methodSplit = preg_split('/(?=[A-Z])/', $method);
			//	remove "get" part
			array_shift($methodSplit);

			$name = '';

			if ($cameCase) {
				$first = false;

				foreach ($methodSplit as $partItem) {
					$partItem = strtolower($partItem);

					if ($first) {
						$partItem = ucfirst($partItem);
					}

					$name .= $partItem;
					$first = true;
				}
			} else {
				$separate = '';

				foreach ($methodSplit as $partItem) {
					$partItem = strtolower($partItem);
					$name .= $separate . $partItem;
					$separate = '_';
				}
			}

			$result[$name] = $this->$method();
		}

		return $result;
	}

}
