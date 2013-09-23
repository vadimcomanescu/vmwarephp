<?php

class ServiceTest extends PHPUnit_Framework_TestCase {

	private $vmwareSoapClient;
	private $vmWareService;
	private $soapClientFactory;
	private $vhost;

	function setUp() {
		$this->soapClientFactory = \Mockery::mock('\Vmwarephp\Factory\SoapClient');
		$this->soapClientFactory->shouldReceive('getClientClassMap');
		$this->vhost = $this->aVhost();
		$this->stubSoapClient();
		$this->vmWareService = new \Vmwarephp\Service($this->vhost, $this->soapClientFactory);
	}

	function testShouldBeAbleToLoginOnTheGivenHost() {
		$sessionManager = $this->aMockerSessionManager();
		$this->stubServiceContent(array('sessionManager' => $sessionManager));

		$sessionManager->shouldReceive('acquireSession')->once()->with($this->vhost->username, $this->vhost->password);

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


	//A value need to be set on any of the property for the mocked object to be of the same type as the extension
	private function aMockerSessionManager() {
		$sessionManager = \Mockery::mock('\Vmwarephp\Extensions\SessionManager');
		$sessionManager->someValue = 'someValue';
		return $sessionManager;
	}

	private function aMockedPropertyCollector() {
		$propertyCollector = \Mockery::mock('\Vmwarephp\Extensions\PropertyCollector');
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
