<?php
namespace Vmwarephp\Extensions;

class Folder extends \Vmwarephp\ManagedObject
{
	public function getChild($type = '', $name = '')
	{
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

		return false;
	}

	/**
	 * @param $path A folder path delimited with '/' like Folder1/Folder2/etc which would attempt to find Folder1 as a child of this Folder, Folder2 as a child of Folder1, etc as a child of Folder2.
	 */
	public function getFolderByPath($path = '')
	{
		if (!$path) {
			throw new \Exception('Folder::getFolderByPath requires a $path argument');
		}
		$folderNames = explode('/', $path);
		$name = $path;
		$newPath = '';

		if (count($folderNames) > 1) {
			$name = $folderNames[0];
			$newPath = str_replace($name.'/', '', $path);
		}

		$childFolder = $this->getChild('Folder', $name);
		if (!$childFolder) {
			return false;
		}
		if (!$newPath) {
			return $childFolder;
		}

		return $childFolder->getFolderByPath($newPath);
	}
}
