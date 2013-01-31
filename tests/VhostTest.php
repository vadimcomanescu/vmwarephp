<?php

class VhostTest extends PHPUnit_Framework_TestCase {
	private $vhost;

	protected function setUp() {
		$this->vhost = new \Vmwarephp\Vhost('vcenter', 'Admin', 'pass');
	}

	function testDefaultConnectionPortIs443() {
		$this->assertEquals('443', $this->vhost->getPort());
	}

	function testItProvidesThePortIfSpecifiedInHostDefinition() {
		$this->vhost->host = 'vcenter:4443';

		$this->assertEquals('4443', $this->vhost->getPort());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	function testRaisesExceptionIfQueriedForAPropertyWhichDoesNotExists() {
		$this->vhost->propertyThatDoesNotExist;
	}

	/**
	 * @expectedException \Vmwarephp\Exception\InvalidVhost
	 * @expectedExceptionMessage Host
	 */
	function testRaisesExceptionWhenHostIsEmpty() {
		new \Vmwarephp\Vhost('', 'Admin', 'pass', 'vCenter');
	}

	/**
	 * @expectedException \Vmwarephp\Exception\InvalidVhost
	 * @expectedExceptionMessage Username
	 */
	function testRaisesExceptionWhenUsernameIsEmpty() {
		new \Vmwarephp\Vhost('vcenter', '', 'pass', 'vCenter');
	}

	function testMethodCallsWillBeRedirectedToTheService() {
		$vmwareService = \Mockery::mock('\Vmwarephp\Service');
		$vmwareService->shouldReceive('getServiceContent')->once();
		$this->vhost->changeService($vmwareService);

		$this->vhost->getServiceContent();
	}
}