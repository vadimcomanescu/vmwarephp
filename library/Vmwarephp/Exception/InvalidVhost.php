<?php
namespace Vmwarephp\Exception;

class InvalidVhost extends \Exception {
	function __construct($message = null, $code = 0, Exception $previous = null) {
		$defaultMessage = 'Invalid vCenter/ESX host provided';
		parent::__construct($message ? : $defaultMessage, $code, $previous);
	}
}
