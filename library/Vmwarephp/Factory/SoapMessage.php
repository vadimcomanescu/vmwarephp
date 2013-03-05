<?php

namespace Vmwarephp\Factory;

class SoapMessage {

	static function makeUsingManagedObject(\Vmwarephp\ManagedObject $managedObject, $arguments = array()) {
		$soapMessage['_this'] = $managedObject->toReference();
		foreach ($arguments as $args) {
			$soapMessage = array_merge($soapMessage, $args);
		}
		return $soapMessage;
	}

	static function makeForServiceInstance() {
		$soapMessage['_this'] = new \SoapVar('ServiceInstance', XSD_STRING, 'ServiceInstance');
		return $soapMessage;
	}
}
