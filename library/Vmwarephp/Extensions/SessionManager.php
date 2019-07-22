<?php
namespace Vmwarephp\Extensions;

class SessionManager extends \Vmwarephp\ManagedObject {

	private $session;

	function acquireSession($userName, $password) {
		if ($this->session) {
			return $this->session;
		}
        $this->session = $this->acquireANewSession($userName, $password);
		return $this->session;
	}

	private function acquireANewSession($userName, $password) {
		$session = $this->Login(array('userName' => $userName, 'password' => $password, 'locale' => null));
		$this->AcquireCloneTicket();
		return $session;
	}
}
