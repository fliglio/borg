<?php

namespace Fliglio\Borg;


class CollectiveWrapper {


	private $collectiveAgent;
	private $collective;
	private $dc;

	public function __construct($agent, Collective $c, $dc = Collective::DEFAULT_AZ) {
		$this->collectiveAgent = $agent;
		$this->collective = $c;
		$this->dc = $dc;
	}
	
	public function mkchan($type) {
		return $this->collective->mkchan($type, $this->dc);
	}

	public function __call($method, array $args) {
		$this->collective->dispatch($this->collectiveAgent, $method, $args, $this->dc);
	}
}

