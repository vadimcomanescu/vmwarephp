<?php
namespace Vmwarephp\Extensions;

class Datastore extends \Vmwarephp\ManagedObject {

	function getConnectedHosts() {
		$hosts = array();
		foreach ($this->host as $hostMount)
			if ($hostMount) $hosts[] = $hostMount->key;
		return $hosts;
	}

	function getVirtualMachinesReferencingThisDatastore() {
		if (!$this->vm[0]) return array();
		return array_filter($this->vm, function ($aVm) {
			return !$aVm->isTemplate();
		});
	}

	function getVirtualMachinesInstalledOnThisDatastore() {
		$vms = array();
		foreach ($this->getVirtualMachinesReferencingThisDatastore() as $vm)
			if ($vm->getParentDatastoreName() == $this->name)
				$vms[] = $vm;
		return $vms;
	}

	function isAccessible() {
		return in_array($this->configStatus, array('green', 'gray'));
	}
}
