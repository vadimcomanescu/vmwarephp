<?php

class PropertyCollectorTest extends PHPUnit_Framework_TestCase {

	private $vmwareService;
	private $propertyCollector;
	private $propertyFilterFactory;

	function setUp() {
		$this->vmwareService = \Mockery::mock('\Vmwarephp\Service[RetrieveProperties]');
		$this->propertyFilterFactory = \Mockery::mock('\Vmwarephp\Factory\PropertyFilterSpec');
		$this->propertyCollector = new \Vmwarephp\Extensions\PropertyCollector($this->vmwareService,
			new ManagedObjectReference('propertyCollector', 'PropertyCollector'), $this->propertyFilterFactory);
	}

	function testCollectsAllManagedObjectsMatchingATypeAndTheirRequestedProperties() {
		$managedObjectsType = $this->aManagedObjectType();
		$somePropertiesToCollect = $this->somePropertiesToCollect();

		$propertyFilterSpec = $this->aPropertyFilterSpec();
		$this->propertyFilterFactory->shouldReceive('makeForTraversingAllInventory')
			->with($managedObjectsType, $somePropertiesToCollect, $this->vmwareService)
			->andReturn($propertyFilterSpec);

		$this->vmwareService->shouldReceive('RetrieveProperties')
			->with($this->propertyCollector, array(array('specSet' => $propertyFilterSpec, 'options' => array())));

		$this->propertyCollector->collectAll($managedObjectsType, $somePropertiesToCollect);
	}

	function testCollectsPropertiesForAGivenManagedObject() {
		$managedObjectType = $this->aManagedObjectType();
		$somePropertiesToCollect = $this->somePropertiesToCollect();
		$managedObjectId = $this->aManagedObjectId();

		$propertyFilterSpec = $this->aPropertyFilterSpec();
		$this->propertyFilterFactory->shouldReceive('makeForOneManagedObject')
			->with($managedObjectType, $managedObjectId, $somePropertiesToCollect)
			->andReturn($propertyFilterSpec);

		$this->vmwareService->shouldReceive('RetrieveProperties')
			->with($this->propertyCollector, array(array('specSet' => $propertyFilterSpec, 'options' => array())));

		$this->propertyCollector->collectPropertiesFor($managedObjectType, $managedObjectId, $somePropertiesToCollect);
	}

	private function aPropertyFilterSpec() {
		$factory = new \Vmwarephp\Factory\PropertyFilterSpec();
		return $factory->makeForOneManagedObject('VirtualMachine', 'vm151', $this->somePropertiesToCollect());
	}

	private function aManagedObjectType() {
		return 'VirtualMachine';
	}

	private function aManagedObjectId() {
		return 'vm151';
	}

	private function somePropertiesToCollect() {
		return array('configStatus');
	}
}