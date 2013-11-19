<?php
namespace Vmwarephp\Extensions;

class Folder extends \Vmwarephp\ManagedObject {
	/**
	 * @param $type The type of ManagedObject to find
	 * @param $name The name of the ManagedObject to find
	 * @param $create Whether or not to create a folder with the name $name if it doesn't exist.
	 */
	public function getChild($type = '', $name = '', $create = false) {
		if (!$type || !$name) {
			throw new \Exception('Folder::getChild requires $type and $child arguments');
		}

		foreach ($this->childEntity as $child) {
			if (!is_object($child)) {
				continue;
			}
			if ($child->getReferenceType() === $type) {
				if ($child->name === $name) {
					return $child;
				}
			}
		}

		if ($create && $type === 'Folder') {
			return $this->createFolder(array('name' => $name));
		}

		return false;
	}

	public function getChildren($type = '')
	{
		if (!$type) {
			return $this->childEntity;
		}

		$children = array();

		foreach ($this->childEntity as $child) {
			if (!is_object($child)) {
				continue;
			}
			if ($child->getReferenceType() === $type) {
				$children[] = $child;
			}
		}

		return $children;
	}

	/**
	 * @param $path A folder path delimited with '/' like Folder1/Folder2/etc which would attempt to find Folder1 as a child of this Folder, Folder2 as a child of Folder1, etc as a child of Folder2.
	 * @param $create Whether or not to create the folders described by the path if they don't exist.
	 */
	public function getFolderByPath($path = '', $create = false) {
		if (!$path) {
			throw new \Exception('Folder::getFolderByPath requires a $path argument');
		}
		$folderNames = explode('/', $path);
		$name = $path;
		$newPath = '';

		if (count($folderNames) > 1) {
			$name = $folderNames[0];
			$newPath = str_replace($name . '/', '', $path);
		}

		$childFolder = $this->getChild('Folder', $name);
		if (!$childFolder) {
			if ($create) {
				$childFolder = $this->createFolder(array('name' => $name));
			} else {
				return false;
			}
		}
		if (!$newPath) {
			return $childFolder;
		}

		return $childFolder->getFolderByPath($newPath, $create);
	}
}
