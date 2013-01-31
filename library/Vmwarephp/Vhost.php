<?php

namespace Vmwarephp;
use Vmwarephp\Exception as Ex;

class Vhost {
	private $service;

	function __construct($host, $username, $password) {
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
	}

	function getPort() {
		$port = parse_url($this->host, PHP_URL_PORT);
		return $port ? : '443';
	}

	function __get($propertyName) {
		if (!isset($this->$propertyName)) throw new \InvalidArgumentException('Property ' . $propertyName . ' not set on this object!');
		return $this->$propertyName;
	}

	function __set($propertyName, $value) {
		$this->validateProperty($propertyName, $value);
		$this->$propertyName = $value;
	}

	function __call($method, $arguments) {
		if (!$this->service) $this->initializeService();
		return call_user_func_array(array($this->service, $method), $arguments);
	}

	function getApiType() {
		return $this->getServiceContent()->about->apiType;
	}

	function changeService(\Vmwarephp\Service $service) {
		$this->service = $service;
	}

	private function initializeService() {
		if (!$this->service)
			$this->service = \Vmwarephp\Factory\Service::makeConnected($this);
	}

	private function validateProperty($propertyName, $value) {
		if (in_array($propertyName, array('host', 'username', 'password')) && empty($value))
			throw new Ex\InvalidVhost('Vhost ' . ucfirst($propertyName) . ' cannot be empty!');
	}
}
