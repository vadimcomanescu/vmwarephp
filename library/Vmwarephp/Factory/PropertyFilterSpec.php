<?php

namespace Vmwarephp\Factory;

class PropertyFilterSpec {

	function makeForOneManagedObject($managedObjectType, $referenceId, $propertiesToCollect) {
		$sets = $this->makeTraversalAndPropSets($managedObjectType, $propertiesToCollect);
		return new \PropertyFilterSpec($sets['propSet'], $this->makeObjectSet($managedObjectType, $referenceId, false, $sets['traversalSet']));
	}

	function makeForTraversingAllInventory($managedObjectType, $propertiesToCollect, \Vmwarephp\Service $service) {
		$containerView = $this->makeContainerView($managedObjectType, $service);
		$sets = $this->makeTraversalAndPropSets($managedObjectType, $propertiesToCollect);
		$traversalSpec = $this->makeTraversalSpec('view', 'ContainerView');
		$traversalSpec->selectSet = $sets['traversalSet'];
		return new \PropertyFilterSpec($sets['propSet'], $this->makeObjectSet($containerView->type, $containerView->_, true, $traversalSpec));
	}

	private function makeTraversalAndPropSets($managedObjectType, $propertiesToCollect) {
		$specs = array('propSet' => array(), 'traversalSet' => array());
		if ($propertiesToCollect == 'all') {
			$specs['propSet'][] = $this->makePropSpec($managedObjectType, $propertiesToCollect);
			return $specs;
		}
		$nonTraversalProperties = array();
		foreach ($propertiesToCollect as $key => $value) {
			if ($this->isATraversalProperty($key)) {
				$this->checkTraversalPropertyFormat($propertiesToCollect, $key);
				$specs['traversalSet'][] = $this->makeTraversalSpec($key, $managedObjectType);
				$specs['propSet'][] = $this->makePropSpec($propertiesToCollect[$key][0], $propertiesToCollect[$key][1]);
			} else {
				$nonTraversalProperties[] = $value;
			}
		}
		$specs['propSet'][] = $this->makePropSpec($managedObjectType, $nonTraversalProperties);
		return $specs;
	}

	private function makeContainerView($managedObjectType, $vmwareService) {
		$viewManager = $vmwareService->getViewManager();
		$containerView = $viewManager->CreateContainerView(
			array('container' => $vmwareService->getRootFolder()->toReference(),
				'recursive' => true, 'type' => $managedObjectType));
		return $containerView->toReference();
	}

	private function makeTraversalSpec($key, $managedObjectType) {
		return new \TraversalSpec('traverse' . ucfirst($key), $managedObjectType, $key, false, null);
	}

	private function makePropSpec($managedObjectType, $propertiesToBeRetrieved) {
		if ($propertiesToBeRetrieved == 'all') return new \PropertySpec($managedObjectType, true);
		return new \PropertySpec($managedObjectType, false, $propertiesToBeRetrieved);
	}

	private function isATraversalProperty($key) {
		return !is_numeric($key);
	}

	private function makeObjectSet($managedObjectType, $referenceId, $skip, $traversalSpecsSelectSet) {
		return array(new \ObjectSpec(new \ManagedObjectReference($referenceId, $managedObjectType), $skip, empty($traversalSpecsSelectSet) ? null : $traversalSpecsSelectSet));
	}

	private function checkTraversalPropertyFormat($propertiesToCollect, $key) {
		if (!isset($propertiesToCollect[$key][0]) || !isset($propertiesToCollect[$key][1]))
			throw new \Vmwarephp\Exception\InvalidTraversalPropertyFormat();
	}
}