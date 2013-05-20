<?php
namespace Vmwarephp\Extensions;

class PropertyCollector extends \Vmwarephp\ManagedObject {

	private $propFilterSpecFactory;

	function __construct(\Vmwarephp\Service $vmwareService, \ManagedObjectReference $managedObjectReference,
						 \Vmwarephp\Factory\PropertyFilterSpec $factory = null) {
		parent::__construct($vmwareService, $managedObjectReference);
		$this->propFilterSpecFactory = $factory ? : new \Vmwarephp\Factory\PropertyFilterSpec();
	}

	function collectAll($managedObjectType, $propertiesToCollect) {
		$propertyFilterSpec = $this->propFilterSpecFactory->makeForTraversingAllInventory($managedObjectType, $propertiesToCollect, $this->vmwareService);
		$managedObjects = $this->getPropertiesUsingSpec($propertyFilterSpec);
		if (!$managedObjects) return array();
		return is_array($managedObjects) ? $managedObjects : array($managedObjects);
	}

	function collectPropertiesFor($managedObjectType, $referenceId, $propertiesToCollect) {
		$propertyFilterSpec = $this->propFilterSpecFactory->makeForOneManagedObject($managedObjectType, $referenceId, $propertiesToCollect);
		$result = $this->getPropertiesUsingSpec($propertyFilterSpec);
		return $this->appendTraversedPropertiesToRequestedObject($result, $propertiesToCollect, $managedObjectType);
	}

	private function getPropertiesUsingSpec($propertyFilterSpec, $options = array()) {
		return $this->RetrieveProperties(array('specSet' => $propertyFilterSpec, 'options' => $options));
	}

	private function appendTraversedPropertiesToRequestedObject($collectionResult, $propertiesToCollect, $managedObjectType) {
		if (!$collectionResult) return;
		$hashedCollectionResult = $this->collectionResultToHash($collectionResult);
		$requestedObject = $this->findRequestedObjectInCollectionResult($collectionResult, $managedObjectType);
		$propertiesToCollect = is_array($propertiesToCollect) ? $propertiesToCollect : array($propertiesToCollect);
		foreach ($propertiesToCollect as $key => $value)
			if ($this->isATraversalProperty($key))
				$requestedObject->$key = isset($hashedCollectionResult[$value[0]]) ? $hashedCollectionResult[$value[0]] : null;
		return $requestedObject;
	}

	private function findRequestedObjectInCollectionResult($collectionResult, $managedObjectType) {
		if (is_object($collectionResult)) return $collectionResult;
		foreach ($collectionResult as $managedObject) {
			if ($managedObject->getReferenceType() == $managedObjectType) return $managedObject;
		}
		throw new \Exception('Cannot find the object we requested to collect the properties for in servers response!');
	}

	private function collectionResultToHash($collectionResult) {
		$hash = array();
		if (!is_array($collectionResult)) return $hash;
		foreach ($collectionResult as $managedObject)
			$hash[$managedObject->getReferenceType()][] = $managedObject;
		return $hash;
	}

	private function isATraversalProperty($propertyKey) {
		return !is_numeric($propertyKey);
	}
}
