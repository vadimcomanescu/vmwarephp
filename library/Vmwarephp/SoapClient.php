<?php
namespace Vmwarephp;

class SoapClient extends \SoapClient {

	function __doRequest($request, $location, $action, $version, $one_way = 0) {
		$request = $this->appendXsiTypeForExtendedDatastructures($request);
		$result = parent::__doRequest($request, $location, $action, $version, $one_way);
		if (isset($this->__soap_fault) && $this->__soap_fault) {
			throw $this->__soap_fault;
		}
		return $result;
	}

	/* PHP does not provide inheritance information for wsdl types so we have to specify that its and xsi:type
	 * php bug #45404
	 * */
	private function appendXsiTypeForExtendedDatastructures($request) {
		$request = str_replace("xsi:", "", $request);
		return str_replace(array("type=\"ns1:TraversalSpec\"", '<ns1:selectSet />'), array("xsi:type=\"ns1:TraversalSpec\"", ''), $request);
	}
}
