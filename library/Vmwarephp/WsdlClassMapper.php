<?php
namespace Vmwarephp;

class WsdlClassMapper {
	private $classDefinitionsFilePath;
	private $classMap;
	private $useClassMapCaching = true;

	function __construct($classDefinitionsFilePath = null) {
		$this->classDefinitionsFilePath = $classDefinitionsFilePath ? : dirname(__FILE__) . '/TypeDefinitions.inc';
	}

	function getClassMap() {
		if (!empty($this->classMap)) return $this->classMap;
		$this->initializeClassMap();
		return $this->classMap;
	}

	function configureClassMapCaching($useCaching = true) {
		$this->useClassMapCaching = $useCaching;
	}

	private function initializeClassMapFromCache() {
		$cacheFilePath = $this->makeCacheFilePath();
		if (!file_exists($cacheFilePath) || !$this->useClassMapCaching) return;
		$this->classMap = unserialize(file_get_contents($cacheFilePath));
	}

	private function initializeClassMap() {
		$this->initializeClassMapFromCache();
		if ($this->classMap) return;
		$this->classMap = $this->generateClassMap();
		$this->cacheClassMap();
	}

	private function generateClassMap() {
		$classMap = array();
		$allTokens = token_get_all($this->readClassDefinitions());
		$namespace = null;
		foreach ($allTokens as $key => $token) {
			if ($this->tokenRepresentsNamespaceDefinition($token))
				$namespace = $allTokens[$key + 2][1];
			if ($this->tokenRepresentsClassDefinition($token)) {
				$className = $allTokens[$key + 2][1];
				$classMap[$className] = $this->makeAbsoluteClassName($namespace, $className);
			}
		}
		return $classMap;
	}

	private function makeAbsoluteClassName($namespace, $className) {
		$namespaceSeparator = '\\';
		return $namespace ? $namespaceSeparator . $namespace . $namespaceSeparator . $className : $className;
	}

	private function tokenRepresentsClassDefinition($token) {
		return is_array($token) && $token[0] == T_CLASS;
	}

	private function tokenRepresentsNamespaceDefinition($token) {
		return is_array($token) && $token[0] == T_NAMESPACE;
	}

	private function readClassDefinitions() {
		if (!file_exists($this->classDefinitionsFilePath)) return '';
		return file_get_contents($this->classDefinitionsFilePath);
	}

	private function cacheClassMap() {
		if (!$this->useClassMapCaching) return;
		file_put_contents($this->makeCacheFilePath(), serialize($this->classMap));
	}

	private function makeCacheFilePath() {
		return dirname(__FILE__) . '/' . '.wsdl_class_map_cache';
	}
}