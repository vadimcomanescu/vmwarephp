<?php
namespace Vmwarephp;

class WsdlClassMapper {
	private $classDefinitionsFilePath;
	private $useClassMapCaching = true;

	function __construct($classDefinitionsFilePath = null) {
		$this->classDefinitionsFilePath = $classDefinitionsFilePath ? : dirname(__FILE__) . '/TypeDefinitions.inc';
	}

	function getClassMap() {
		$classMap = $this->readClassMapFromCache();
		if ($classMap) {
			return $classMap;
		}
		$classMap = $this->generateClassMap();
		$this->cacheClassMap($classMap);
		return $classMap;
	}

	function configureClassMapCaching($useCaching = true) {
		$this->useClassMapCaching = $useCaching;
	}

	private function readClassMapFromCache() {
		$cacheFilePath = $this->makeCacheFilePath();
		if (!file_exists($cacheFilePath) || !$this->useClassMapCaching) return;
		return unserialize(file_get_contents($cacheFilePath));
	}

	private function generateClassMap() {
		$classMap = array();
		$allTokens = token_get_all($this->readClassDefinitions());
		foreach ($allTokens as $key => $token) {
			if ($this->tokenRepresentsClassDefinition($token)) {
				$className = $allTokens[$key + 2][1];
				$classMap[$className] = $className;
			}
		}
		return array_merge($classMap, $this->getExtendedClasses());
	}

	private function getExtendedClasses() {
		$classes = array();
		foreach (scandir(__DIR__ . '/Extensions/') as $fileName) {
			if (in_array($fileName, array('.', '..'))) continue;
			$classNameComponents = explode('.', $fileName);
			$className = $classNameComponents[0];
			$classes[$className] = '\\Vmwarephp\\Extensions\\' . $className;
		}
		return $classes;
	}

	private function tokenRepresentsClassDefinition($token) {
		return is_array($token) && $token[0] == T_CLASS;
	}

	private function readClassDefinitions() {
		if (!file_exists($this->classDefinitionsFilePath)) return '';
		return file_get_contents($this->classDefinitionsFilePath);
	}

	private function cacheClassMap($classMap) {
		if (!$this->useClassMapCaching) return;
		file_put_contents($this->makeCacheFilePath(), serialize($classMap));
	}

	private function makeCacheFilePath() {
		return dirname(__FILE__) . '/' . '.wsdl_class_map_cache';
	}
}