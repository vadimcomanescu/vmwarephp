<?php

namespace Vmwarephp\Factory;

class Service {

	static function make(\Vmwarephp\Vhost $vhost) {
		return new \Vmwarephp\Service($vhost);
	}

	static function makeConnected(\Vmwarephp\Vhost $vhost) {
		$service = self::make($vhost);
		$service->connect();
		return $service;
	}
}