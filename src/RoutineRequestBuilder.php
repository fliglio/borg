<?php

namespace Fliglio\Borg;

class RoutineRequestBuilder {

	private $ns;
	private $dc;
	private $type;
	private $method;
	
	private $args = [];
	private $retryErrors;

	public function ns($ns) {
		$this->ns = $ns;
		return $this;
	}
	public function dc($dc) {
		$this->dc = $dc;
		return $this;
	}
	public function type($type) {
		$this->type = $type;
		return $this;
	}
	public function method($method) {
		$this->method = $method;
		return $this;
	}
	public function args(array $args) {
		$this->args = $args;
		return $this;
	}
	public function retryErrors($r) {
		$this->retryErrors = $r;
		return $this;
	}

	public function build() {
		return new RoutineRequest(
			$this->ns,
			$this->dc,
			$this->type,
			$this->method,
			$this->args,
			$this->retryErrors
		);
	}

}