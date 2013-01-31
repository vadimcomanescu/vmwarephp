<?php
namespace Vmwarephp\Exception;

class CannotCreateSoapClient extends \Exception {
	function __construct($message = null, $code = 0, Exception $previous = null) {
		$defaultMessage = 'Cannot create a SOAP client!';
		parent::__construct($message ? : $defaultMessage, $code, $previous);
	}
}
