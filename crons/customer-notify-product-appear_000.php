<?
require_once __DIR__ . '/bootstrap.php';

(new \Ego\Crons\CustomerNotifyProductAppear($registry))->execute();
