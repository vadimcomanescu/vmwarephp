<?php

class SoapClientTest extends PHPUnit_Framework_TestCase {
	private $factory;
	private $wsdlClassMapper;

	protected function setUp() {
		$this->wsdlClassMapper = \Mockery::mock('\Vmwarephp\WsdlClassMapper');
		$this->factory = new \Vmwarephp\Factory\SoapClient($this->wsdlClassMapper);
	}

	function testMakesASoapClient() {
		$vhost = $this->aVhost();
		$aClassMap = $this->aClassMap();
		$this->wsdlClassMapper->shouldReceive('getClassMap')->andReturn($aClassMap);

		$soapClient = $this->factory->make($vhost);

		$this->assertEquals(SOAP_SINGLE_ELEMENT_ARRAYS + SOAP_USE_XSI_ARRAY_TYPE, $soapClient->_features);
		$this->assertEquals('https://' . $vhost->host . '/sdk', $soapClient->location);
		$this->assertEquals($aClassMap, $soapClient->_classmap);
	}

	private function aVhost() {
		$vhost = \Mockery::mock('\Vmwarephp\Vhost');
		$vhost->host = 'a_host';
		return $vhost;
	}

	private function aClassMap() {
		return array('klass' => 'klass');
	}
}