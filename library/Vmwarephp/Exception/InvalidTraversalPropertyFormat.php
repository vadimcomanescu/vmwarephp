<?php
namespace Vmwarephp\Exception;

class InvalidTraversalPropertyFormat extends \Exception {

	function __construct($message = null, $code = 0, Exception $previous = null) {
		$defaultMessage = 'Invalid traversal property format provided. Valid traversal property format is: propertyName => array(propertyType, arrayOfPropertyTypeProperties)';
		parent::__construct($message ? : $defaultMessage, $code, $previous);
	}
}
