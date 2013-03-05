<?php

class SoapMessageTest extends PHPUnit_Framework_TestCase {

	function testItMakesAMessageForGettingTheServiceInstance() {
		$expectedMessage = array('_this' => new \SoapVar('ServiceInstance', XSD_STRING, 'ServiceInstance'));

		$this->assertEquals($expectedMessage, \Vmwarephp\Factory\SoapMessage::makeForServiceInstance());
	}

	function testItMakesASoapMessageForAMethodCallOnAManagedObject() {
		$service = \Mockery::mock('\Vmwarephp\Service');
		$reference = new ManagedObjectReference('vm151', 'VirtualMachine');
		$managedObject = new \Vmwarephp\ManagedObject($service, $reference);

		# Method invocation arguments will be passed as an array of arrays
		$arguments = array(array('firstArgument'), array('secondArgument'));
		$expectedMessage = array('_this' => $reference, 'firstArgument', 'secondArgument');
		$this->assertEquals($expectedMessage, \Vmwarephp\Factory\SoapMessage::makeUsingManagedObject($managedObject, $arguments));
	}

}