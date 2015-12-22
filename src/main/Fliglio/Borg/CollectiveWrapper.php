<?php

namespace Fliglio\Borg;


class CollectiveWrapper {


	private $drone;
	private $collective;
	private $dc;

	public function __construct($drone, Collective $c, $dc) {
		$this->drone = $drone;
		$this->collective = $c;
		$this->dc = $dc;
	}
	
	public function mkchan($type) {
		return $this->collective->mkchan($type, $this->dc);
	}
	

	/**
	 * Using the magic __call method to capture the desired method to call, 
	 * dispatch a Collective async routine request
	 */
	public function __call($method, array $args) {
		$this->collective->dispatch($this->drone, $method, $args, $this->dc);
	}
}

