<?php
namespace Vmwarephp\Exception;

class InvalidVhost extends \Exception {
	function __construct($message = null, $code = 0, Exception $previous = null) {
		$defaultMessage = 'Invalid vCenter/ESX host provided';
		$message ? parent::__construct($message, $code, $previous) : parent::__construct($defaultMessage, $code, $previous);
	}
}
