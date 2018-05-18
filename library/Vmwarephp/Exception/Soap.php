<?php
namespace Vmwarephp\Exception;

class Soap extends \Exception {

	function __construct(\SoapFault $soapFault) {
		parent::__construct($this->makeMessage($soapFault), 0, null);
	}

	private function makeMessage($soapFault) {
		$message = $this->makeMessageHeader($soapFault);
		$message .= $this->makeMessageBody($soapFault);
		$message .= $this->makeFaultDetailsString($soapFault);
		return $message;
	}

	private function makeMessageBody($soapFault) {
		return isset($soapFault->string) ? "{$soapFault->string} " : '';
	}

	private function makeMessageHeader($soapFault) {
		return "{$soapFault->faultcode}: {$soapFault->faultstring}. ";
	}

	private function makeFaultDetailsString(\SoapFault $soapFault) {
		$faults = array();
        // \SoapFault::$detail property can be unexistent
        // @link https://bugs.php.net/bug.php?id=46792
        if (isset($soapFault->detail)) {
            foreach ($soapFault->detail as $fault) {
                $faults[] = "{$fault->enc_stype}: " . print_r($fault->enc_value, true);
            }
        }

		return implode(', ', $faults);
	}
}
