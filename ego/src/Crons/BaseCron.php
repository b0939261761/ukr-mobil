<?php

namespace Ego\Crons;

class BaseCron {

	protected $registry;

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}

	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * Public execute
	 */
	public function execute() {
		$this->_execute();
	}

	/**
	 * Internal cron execution
	 */
	protected function _execute() {
		//  Implementation
	}

}
