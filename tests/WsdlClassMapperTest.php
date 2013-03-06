<?php

class WsdlClassMapperTest extends PHPUnit_Framework_TestCase {

	private $wsdlClassMapper;
	private static $classDefinitionsFile;

	static function setUpBeforeClass() {
		self::$classDefinitionsFile = sprintf('/tmp/' . uniqid());
	}

	static function tearDownAfterClass() {
		@unlink(self::$classDefinitionsFile);
	}

	protected function setUp() {
		$this->wsdlClassMapper = new Vmwarephp\WsdlClassMapper(self::$classDefinitionsFile);
		$this->wsdlClassMapper->configureClassMapCaching(false);
	}

	function testCanProvideAClassMapWhenWsdlClassDefinitionsAreProvided() {
		$this->writeWsdlClassDefinitions("<?php \n class Klass {} \n class Klass1 {}");

		$classMap = $this->wsdlClassMapper->getClassMap();
		foreach (array('Klass' => 'Klass', 'Klass1' => 'Klass1') as $key => $value) {
			$this->assertEquals($value, $classMap[$key]);
		}
	}

	private function writeWsdlClassDefinitions($classDefinitions) {
		file_put_contents(self::$classDefinitionsFile, $classDefinitions);
	}
}
