<?php

class PropertyFilterSpecTest extends PHPUnit_Framework_TestCase {

	private $factory;

	protected function setUp() {
		$this->factory = new \Vmwarephp\Factory\PropertyFilterSpec();
	}

	function testMakesAPropertyFilterSpecForGatheringAListOrPropertiesForAManagedObject() {
		$requestedProperties = array('configStatus');
		$referenceId = 'vm-151';
		$managedObjectType = 'VirtualMachine';

		$propSet = array(new \PropertySpec($managedObjectType, false, $requestedProperties));
		$objectSet = array(new \ObjectSpec(new \ManagedObjectReference($referenceId, $managedObjectType), false, null));

		$expectedPropertyFilterSpec = new \PropertyFilterSpec($propSet, $objectSet);
		$this->assertEquals($expectedPropertyFilterSpec, $this->factory->makeForOneManagedObject($managedObjectType, $referenceId, $requestedProperties));
	}

	function testMakesAPropertyFilterSpecForGatheringAllPropertiesForAManagedObject() {
		$managedObjectType = 'VirtualMachine';
		$requestedProperties = 'all';
		$referenceId = 'vm-151';

		$propSet = array(new \PropertySpec($managedObjectType, true));
		$objectSet = array(new \ObjectSpec(new \ManagedObjectReference($referenceId, $managedObjectType), false, null));

		$expectedPropertyFilterSpec = new \PropertyFilterSpec($propSet, $objectSet);

		$this->assertEquals($expectedPropertyFilterSpec, $this->factory->makeForOneManagedObject($managedObjectType, $referenceId, $requestedProperties));
	}

	function testMakesAPropertyFilterSpecForGatheringTraversalPropertiesForAManagedObject() {
		# Gather vm configStatus, go to network and gather its configStatus
		$managedObjectType = 'VirtualMachine';
		$requestedProperties = array('configStatus', 'network' => array('Network', array('configStatus')));
		$referenceId = 'vm-151';

		$traversalSpecs = array(new \TraversalSpec('traverseNetwork', $managedObjectType, 'network', false));
		$propSet = array(new \PropertySpec('Network', false, array('configStatus')), new \PropertySpec($managedObjectType, false, array('configStatus')));
		$objectSet = array(new \ObjectSpec(new \ManagedObjectReference($referenceId, $managedObjectType), false, $traversalSpecs));

		$expectedPropertyFilterSpec = new \PropertyFilterSpec($propSet, $objectSet);
		$this->assertEquals($expectedPropertyFilterSpec, $this->factory->makeForOneManagedObject($managedObjectType, $referenceId, $requestedProperties));
	}

	function testMakesAPropertyFilterSpecForTraversingAllInventoryForAGivenManagedObjectTypeAndItsProperties() {
		$requestedProperties = array('configStatus');
		$managedObjectType = 'VirtualMachine';

		$vmwareService = \Mockery::mock('\Vmwarephp\Service');
		$aContainerView = $this->mockContainerViewUsingService($vmwareService, $managedObjectType);
		$containerViewReference = $aContainerView->toReference();

		$propSet = array(new \PropertySpec($managedObjectType, false, $requestedProperties));
		$traversalSpec = new \TraversalSpec('traverseView', 'ContainerView', 'view', false, array());
		$objectSet = array(new \ObjectSpec($containerViewReference, true, $traversalSpec));

		$expectedPropertyFilterSpec = new \PropertyFilterSpec($propSet, $objectSet);
		$this->assertEquals($expectedPropertyFilterSpec, $this->factory->makeForTraversingAllInventory($managedObjectType, $requestedProperties, $vmwareService));
	}

	private function mockContainerViewUsingService($vmwareService, $managedObjectType) {
		$aContainerManager = $this->aManagedObject();
		$vmwareService->shouldReceive('getViewManager')->once()->andReturn($aContainerManager);
		$rootFolder = $this->aManagedObjectThatExpectsToBeCastToReference();
		$vmwareService->shouldReceive('getRootFolder')->once()->andReturn($rootFolder);
		$aContainerView = $this->aManagedObjectThatExpectsToBeCastToReference(new ManagedObjectReference('1', 'ContainerView'));
		$aContainerManager->shouldReceive('CreateContainerView')->with(array('container' => $rootFolder->toReference(),
			'recursive' => true, 'type' => $managedObjectType))->andReturn($aContainerView);
		return $aContainerView;
	}

	private function aManagedObjectThatExpectsToBeCastToReference($reference = null) {
		$managedObject = $this->aManagedObject();
		$managedObject->shouldReceive('toReference')->atLeast($this->once())->andReturn($reference);
		return $managedObject;
	}

	private function aManagedObject() {
		return \Mockery::mock('\Vmwarephp\ManagedObject');
	}
}