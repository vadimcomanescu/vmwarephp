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
        $this->writeWsdlClassDefinitions("<?php namespace Vmwarephp;\n class Klass {} \n class Klass1 {}");

        $expectedClassMap = array('Klass' => 'Vmwarephp\Klass', 'Klass1' => 'Vmwarephp\Klass1');
        $this->assertEquals($expectedClassMap, $this->wsdlClassMapper->getClassMap());
    }

    function testClassMapIsEmptyWhenClassDefinitionsAreNotInAPhpFile() {
        $this->writeWsdlClassDefinitions("class Klass {} \n class Klass1 {}");

        $this->assertEmpty($this->wsdlClassMapper->getClassMap());
    }

    function testClassMapDoesNotContainDuplicates() {
        $this->writeWsdlClassDefinitions("<?php namespace Vmwarephp;\n class Klass {} \n class Klass {}");

        $expectedClassMap = array('Klass' => 'Vmwarephp\Klass');
        $this->assertEquals($expectedClassMap, $this->wsdlClassMapper->getClassMap());
    }

    private function writeWsdlClassDefinitions($classDefinitions) {
        file_put_contents(self::$classDefinitionsFile, $classDefinitions);
    }
}
