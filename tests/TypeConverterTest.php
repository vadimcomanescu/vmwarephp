<?php

class TypeConverterTest extends PHPUnit_Framework_TestCase {

	private $typeConverter;
	private $vmwareService;
	private $factory;

	protected function setUp() {
		$this->vmwareService = \Mockery::mock('\Vmwarephp\Service');
		$this->factory = new \Vmwarephp\Factory\ManagedObject();
		$this->typeConverter = new \Vmwarephp\TypeConverter($this->vmwareService, $this->factory);
	}

	function testAnEmptyStdClassBecomesANullValue() {
		$result = $this->typeConverter->convert(new stdClass());

		$this->assertNull($result);
	}

	function testAPrimitiveBasicTypeRemainsABasicType() {
		$basicTypesValues = array("value", true, 4, 5.6);

		foreach ($basicTypesValues as $value)
			$this->assertEquals($value, $this->typeConverter->convert($value));
	}

	function testAnArrayOfStringsTypeBecomesAPhpArrayOfStrings() {
		$obj1 = new \ArrayOfString(array('1', '2'));
		$obj2 = new \ArrayOfString('1');

		$this->assertEquals(array('1', '2'), $this->typeConverter->convert($obj1));
		$this->assertEquals(array('1'), $this->typeConverter->convert($obj2));
	}

	function testAManagedObjectReferenceBecomesAManagedObject() {
		$reference = $this->factory->makeReference('datacenter-21', 'Datacenter');
		$managedObject = $this->factory->make($this->vmwareService, $reference);

		$this->assertEquals($managedObject, $this->typeConverter->convert($reference));
	}

	function testAnArrayOfManagedObjectReferenceTypeBecomesAPhpArrayOfManagedObjects() {
		$reference = $this->factory->makeReference('datastore-82', 'Datastore');
		$obj = new \ArrayOfManagedObjectReference($reference);

		$expectedValue = array($this->factory->make($this->vmwareService, $reference));
		$this->assertEquals($expectedValue, $this->typeConverter->convert($obj));
	}

	function testAnArrayOfAnyTypeBecomesAnArrayOfObjectsWithAllTheirPropertiesConverted() {
		$alarmState1 = new \AlarmState("alarm-1.datacenter-21", $this->factory->makeReference('group-d1', 'Folder'));
		$alarmState2 = new \AlarmState("alarm-2.datacenter-21", $this->factory->makeReference('group-d2', 'Folder'));

		$anyTypeArray = new \ArrayOfAnyType(array($alarmState1, $alarmState2));

		$alarmState1Converted = new \AlarmState("alarm-1.datacenter-21",
			$this->factory->make($this->vmwareService, $this->factory->makeReference('group-d1', 'Folder')));
		$alarmState2Converted = new \AlarmState("alarm-2.datacenter-21",
			$this->factory->make($this->vmwareService, $this->factory->makeReference('group-d2', 'Folder')));

		$convertedValue = array($alarmState1Converted, $alarmState2Converted);
		$this->assertEquals($convertedValue, $this->typeConverter->convert($anyTypeArray));
	}

	// For a detailed definition of object content see sdk documentation: http://pubs.vmware.com/vsphere-51/index.jsp
	function testAnObjectContentBecomesAManagedObjectWithProperties() {
		$taskReference = $this->factory->makeReference('task-123', 'Task');
		$dynamicProperty = new \DynamicProperty('recentTask', $taskReference);
		$vmReference = $this->factory->makeReference('vm-114', 'VirtualMachine');
		$objectContent = new \ObjectContent($vmReference, array($dynamicProperty));

		$virtualMachine = $this->typeConverter->convert($objectContent);

		$this->assertEquals($this->factory->make($this->vmwareService, $taskReference), $virtualMachine->recentTask);
	}

	function testAnObjectContentWithoutDynamicPropertyBecomesAManagedObjectWithoutAnyProperties() {
		$vmReference = $this->factory->makeReference('vm-114', 'VirtualMachine');
		$objectContent = new \ObjectContent($vmReference, null);

		$virtualMachine = $this->typeConverter->convert($objectContent);
		$this->assertEmpty(get_object_vars($virtualMachine));
	}
}
