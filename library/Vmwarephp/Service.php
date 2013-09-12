<?php

namespace Vmwarephp;

class Service {
	private $soapClient;
	private $vhost;
	private $typeConverter;
	private $serviceContent;
	private $session;
	private $clientFactory;
	private $cloneTicketCache = 'cloneTicket.cache';

	function __construct(Vhost $vhost, \Vmwarephp\Factory\SoapClient $soapClientFactory = null) {
		$this->vhost = $vhost;
		$this->clientFactory = $soapClientFactory ? : new \Vmwarephp\Factory\SoapClient();
		$this->soapClient = $this->clientFactory->make($this->vhost);
		$this->typeConverter = new TypeConverter($this);
		$this->cloneTicketCache = dirname(__FILE__).'/'.$this->cloneTicketCache;
	}

	function __call($method, $arguments) {
		if ($this->isMethodAPropertyRetrieval($method)) return $this->getQueriedProperty($method, $arguments);
		$managedObject = $arguments[0];
		$actionArguments = isset($arguments[1]) ? $arguments[1] : array();
		return $this->makeSoapCall($method, \Vmwarephp\Factory\SoapMessage::makeUsingManagedObject($managedObject, $actionArguments));
	}

	function findAllManagedObjects($objectType, $propertiesToCollect) {
		$propertyCollector = $this->getPropertyCollector();
		return $propertyCollector->collectAll($objectType, $propertiesToCollect);
	}

	function findOneManagedObject($objectType, $referenceId, $propertiesToCollect) {
		$propertyCollector = $this->getPropertyCollector();
		return $propertyCollector->collectPropertiesFor($objectType, $referenceId, $propertiesToCollect);
	}

	function findManagedObjectByName($objectType, $name, $propertiesToCollect = array()) {
		$propertiesToCollect = array_merge($propertiesToCollect, array('name'));
		$allObjects = $this->findAllManagedObjects($objectType, $propertiesToCollect);
		$objects = array_filter($allObjects, function ($object) use ($name) {
			return $object->name == $name;
		});
		return empty($objects) ? null : end($objects);
	}

	function connect() {
		if ($this->session) return $this->session;
		$sessionManager = $this->getSessionManager();
		$this->session = $this->connectWithCloneTicket();
		if (!$this->session)
			$this->session = $sessionManager->Login(array('userName' => $this->vhost->username, 'password' => $this->vhost->password, 'locale' => null));
		$cloneTicket = $sessionManager->AcquireCloneTicket();
		if (!file_put_contents($this->cloneTicketCache, $cloneTicket))
			throw new \Exception('There was an error writing to the clone ticket path. Check the permissions of the cache directory.');
		return $this->session;
	}

	private function connectWithCloneTicket() {
		$cloneTicket = file_get_contents($this->cloneTicketCache);
		if (!$cloneTicket) {
			return false;
		}
		try {
			return $this->getSessionManager()->CloneSession(array('cloneTicket' => $cloneTicket));
		} catch (\Exception $e) {
			return false;
		}
	}

	function getServiceContent() {
		if (!$this->serviceContent)
			$this->serviceContent = $this->makeSoapCall('RetrieveServiceContent', \Vmwarephp\Factory\SoapMessage::makeForServiceInstance());
		return $this->serviceContent;
	}

	protected function convertResponse($response) {
		$responseVars = get_object_vars($response);
		if (isset($response->returnval) || (array_key_exists('returnval', $responseVars) && is_null($responseVars['returnval'])))
			return $this->typeConverter->convert($response->returnval);
		return $this->typeConverter->convert($response);
	}

	private function makeSoapCall($method, $soapMessage) {
		$this->soapClient->_classmap = $this->clientFactory->getClientClassMap();
		try {
			$result = $this->soapClient->$method($soapMessage);
		} catch (\SoapFault $sf) {
			$this->soapClient->_classmap = null;
			$faults = array();
			foreach ($sf->detail as $fault) {
				$faults[] = "{$fault->enc_stype}: ".print_r($fault->enc_value, true);
			}
			$message = "{$sf->faultcode}: {$sf->faultstring}. ";
			if ($sf->string) {
				$message .= "{$sf->string} ";
			}
			if (count($faults)) {
				$message .= implode(', ', $faults);
			}
			throw new \Exception($message);
		}
		$this->soapClient->_classmap = null;
		return $this->convertResponse($result);
	}

	private function getQueriedProperty($method, $arguments) {
		$propertyToRetrieve = $this->generateNameForThePropertyToRetrieve($method);
		$content = $this->getServiceContent();
		if (isset($content->$propertyToRetrieve)) return $content->$propertyToRetrieve;
		$managedObject = $arguments[0];
		$foundManagedObject = $this->findOneManagedObject($managedObject->getReferenceType(),
			$managedObject->getReferenceId(), array($propertyToRetrieve));
		return $foundManagedObject->$propertyToRetrieve;
	}

	private function isMethodAPropertyRetrieval($calledMethod) {
		return preg_match('/^get/', $calledMethod);
	}

	private function generateNameForThePropertyToRetrieve($calledMethod) {
		return lcfirst(substr($calledMethod, 3));
	}
}
