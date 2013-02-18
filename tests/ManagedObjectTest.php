<?php

class ManagedObjectTest extends PHPUnit_Framework_TestCase {

	private $vmwareService;
	private $managedObject;

	protected function setUp() {
		$this->vmwareService = \Mockery::mock('Vmwarephp\Service');
		$this->managedObject = new \Vmwarephp\ManagedObject($this->vmwareService, new ManagedObjectReference('vm-115', 'VirtualMachine'));
	}

	function testIfPropertyIsSetOnTheObjectItNeverQueriesTheService() {
		$this->managedObject->name = 'name';

		$this->vmwareService->shouldReceive('getName')->never();

		$this->assertEquals('name', $this->managedObject->name);
	}

	function testIfPropertyIsNotSetOnTheObjectItIsQueriedThroughTheService() {
		$this->vmwareService->shouldReceive('getName')->with($this->managedObject, array());

		$this->managedObject->name;
	}

	function testAMethodCallIsRedirectedToTheService() {
		$this->vmwareService->shouldReceive('doSomething')->with($this->managedObject, array());

		$this->managedObject->doSomething();
	}

	function testTransformsIntoAManagedObjectReference() {
		$this->assertInstanceOf('ManagedObjectReference', $this->managedObject->toReference());
	}
}