<?php

class DoiTest extends PHPUnit_Framework_TestCase {
	
	
	function testCici() {
        $doi = new Doi();
        $this->assertNull($doi->trei());

        $this->assertFalse(false);
		$this->assertTrue(true);
	}
}