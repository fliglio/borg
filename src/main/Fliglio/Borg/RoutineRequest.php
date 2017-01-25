<?php

namespace Fliglio\Borg;

class RoutineRequest {

	private $ns;
	private $dc;
	private $type;
	private $method;
	
	private $args;
	private $retryErrors;

	public function __construct($ns, $dc, $type, $method, array $args = [], $retryErrors = false) {
		$this->ns = $this->validate($ns);
		$this->dc = $this->validate($dc);
		$this->type = $this->validate($type);
		$this->method = $this->validate($method);
		$this->args = $args;
		$this->retryErrors = $retryErrors;
	}

	public function getNs() {
		return $this->ns;
	}
	public function getDc() {
		return $this->dc;
	}
	public function getType() {
		return $this->type;
	}
	public function getMethod() {
		return $this->method;
	}
	public function getArgs() {
		return $this->args;
	}
	public function getRetryErrors() {
		return $this->retryErrors;
	}

	private function validate($str) {
		if (strpos($str, '.') !== false) {
			throw new \Exception(sprintf("Request component cannot have '.': '%s'", $str));
		}
		return $str;
	}
}
