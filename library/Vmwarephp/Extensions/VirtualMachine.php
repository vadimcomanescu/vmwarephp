<?php
namespace Vmwarephp\Extensions;

class VirtualMachine extends \Vmwarephp\ManagedObject {

	function takeSnapshot($name, $memory = null, $quiesce = null) {
		$snapshotTask = $this->CreateSnapshot_Task(array('name' => $name, 'memory' => $memory, 'quiesce' => $quiesce));
		return $snapshotTask;
	}

	function isAccessible() {
		return in_array($this->configStatus, array('green', 'gray'));
	}

	function isTemplate() {
		return $this->summary->config->template;
	}

	function getParentDatastoreName() {
		preg_match('/\[(.*)\]/', $this->summary->config->vmPathName, $matches);
		return $matches[1];
	}

	function getParentDatastore() {
		foreach ($this->datastore as $datastore)
			if ($datastore->name == $this->getParentDatastoreName()) return $datastore;
	}

	function hasSnapshots() {
		return $this->snapshot ? true : false;
	}

	function getUsedSpace() {
		return $this->summary->storage->committed;
	}

	function getProvisionedSpace() {
		return $this->summary->storage->committed + $this->summary->storage->uncommitted;
	}
}
