<?php

class ServiceTest extends PHPUnit_Framework_TestCase {

	private $vmwareSoapClient;
	private $vmWareService;
	private $soapClientFactory;

	function setUp() {
		$this->soapClientFactory = \Mockery::mock('\Vmwarephp\Factory\SoapClient');
		$this->soapClientFactory->shouldReceive('getClientClassMap');
		$this->stubSoapClient();
		$this->vmWareService = new \Vmwarephp\Service($this->aVhost(), $this->soapClientFactory);
	}

	function testShouldProvideTheServiceContent() {
		$rootFolderReference = new \ManagedObjectReference('folder', 'Folder');
		$this->stubServiceContent(array('rootFolder' => $rootFolderReference));

		$expectedContent = new \ServiceContent(new \Vmwarephp\ManagedObject($this->vmWareService, $rootFolderReference));
		$this->assertEquals($expectedContent, $this->vmWareService->getServiceContent());
	}

	function testShouldProvideAManagedObjectFromTheServiceContent() {
		$rootFolderReference = new \ManagedObjectReference('folder', 'Folder');
		$this->stubServiceContent(array('rootFolder' => $rootFolderReference));

		$rootFolder = new \Vmwarephp\ManagedObject($this->vmWareService, $rootFolderReference);
		$this->assertEquals($rootFolder, $this->vmWareService->getRootFolder());
	}

	function testShouldBeAbleToLoginOnTheGivenHost() {
		$sessionManager = new \ManagedObjectReference('sessionManager', 'SessionManager');
		$this->stubServiceContent(array('sessionManager' => $sessionManager));

		$this->vmwareSoapClient->expects($this->once())
			->method('Login')
			->with(array('_this' => $sessionManager, 'userName' => 'username', 'password' => 'password', 'locale' => ''))
			->will($this->returnValue(new \LoginResponse('a session')));

		$this->vmWareService->connect();
	}

	function testRemotelyCallsAMethodOnAManagedObject() {
		$aManagedObject = $this->aManagedObject();

		# We need to use a method defined in the vimService so we will use the CreateSnapshot_Task defined on a VirtualMachine
		$this->vmwareSoapClient->expects($this->once())->method('CreateSnapshot_Task')
			->with(\Vmwarephp\Factory\SoapMessage::makeUsingManagedObject($aManagedObject, array()))
			->will($this->returnValue(new stdClass()));

		$this->vmWareService->__call('CreateSnapshot_Task', array($aManagedObject, array()));
	}

	function testRetrievesAPropertyForAManagedObjectUsingThePropertyCollector() {
		$aManagedObject = $this->aManagedObject();
		$propertyCollector = $this->aMockedPropertyCollector();
		$this->stubServiceContent(array('propertyCollector' => $propertyCollector));

		$foundManagedObject = $aManagedObject;
		$foundManagedObject->configStatus = 'someConfigStatus';

		$propertyCollector->shouldReceive('collectPropertiesFor')
			->with($aManagedObject->getReferenceType(), $aManagedObject->getReferenceId(), array('configStatus'))
			->andReturn($foundManagedObject);

		$this->assertEquals('someConfigStatus', $this->vmWareService->__call('getConfigStatus', array($aManagedObject)));
	}

	private function aMockedPropertyCollector() {
		$propertyCollector = \Mockery::mock('\Vmwarephp\PropertyCollector');
		$propertyCollector->someValue = 'someValue';
		return $propertyCollector;
	}

	private function aManagedObject() {
		return new \Vmwarephp\ManagedObject($this->vmWareService, new ManagedObjectReference('vm151', 'VirtualMachine'));
	}

	private function stubServiceContent($hash) {
		$serviceContent = new \ServiceContent();
		foreach ($hash as $key => $value) {
			$serviceContent->$key = $value;
		}
		$this->vmwareSoapClient->expects($this->any())
			->method('RetrieveServiceContent')
			->with(\Vmwarephp\Factory\SoapMessage::makeForServiceInstance())
			->will($this->returnValue(new \RetrieveServiceContentResponse($serviceContent)));
	}

	private function stubSoapClient() {
		$this->vmwareSoapClient = clone $this->getMockFromWsdl(__DIR__ . '/../library/Vmwarephp/Wsdl/vimService.wsdl');
		$this->soapClientFactory->shouldReceive('make')->andReturn($this->vmwareSoapClient);
	}

	private function aVhost() {
		return new \Vmwarephp\Vhost('host', 'username', 'password');
	}
}
