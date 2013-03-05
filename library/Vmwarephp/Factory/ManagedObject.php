<?php
namespace Vmwarephp\Factory;

class ManagedObject {
	private $wsdlClassMapper;

	function __construct(\Vmwarephp\WsdlClassMapper $classMapper = null) {
		$this->wsdlClassMapper = $classMapper ? : new \Vmwarephp\WsdlClassMapper;
	}

	function make(\Vmwarephp\Service $service, \ManagedObjectReference $reference) {
		$classMap = $this->wsdlClassMapper->getClassMap();
		if (array_key_exists($reference->type, $classMap)) {
			$className = $classMap[$reference->type];
			return new $className($service, $reference);
		}
		return new \Vmwarephp\ManagedObject($service, $reference);
	}

	function makeGeneratingReference(\Vmwarephp\Service $service, $id, $type) {
		return $this->make($service, $this->makeReference($id, $type));
	}

	function makeReference($id, $type) {
		return new \ManagedObjectReference($id, $type);
	}
}
