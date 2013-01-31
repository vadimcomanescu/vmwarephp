<?php
namespace Vmwarephp\Factory;

class ManagedObject {
	private $classMapper;

	function __construct(\Vmwarephp\WsdlClassMapper $classMapper = null) {
		$this->classMapper = $classMapper ? : new \Vmwarephp\WsdlClassMapper;
	}

	function make(\Vmwarephp\Service $service, \Vmwarephp\ManagedObjectReference $reference) {
		return new \Vmwarephp\ManagedObject($service, $reference);
	}

	function makeGeneratingReference(\Vmwarephp\Service $service, $id, $type) {
		return $this->make($service, $this->makeReference($id, $type));
	}

	function makeReference($id, $type) {
		return new \Vmwarephp\ManagedObjectReference($id, $type);
	}
}
