<?php

namespace Ego\Models;

class BaseModel {


	/** @var \PDO */
	static private $db;

	/**
	 * BaseModel constructor.
	 */
	public function __construct() {
	}

	/**
	 * @return \PDO
	 */
	public function _getDb() {
		if (empty(self::$db)) {
			self::$db = new \PDO("mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
			self::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			self::$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			self::$db->exec('set names utf8');
		}

		return self::$db;
	}

}
