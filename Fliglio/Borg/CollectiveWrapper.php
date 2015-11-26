<?php

namespace Fliglio\Borg;


class CollectiveWrapper {

	private $collectiveAgent;
	private $collective;

	public function __construct($agent, Collective $c) {
		$this->collectiveAgent = $agent;
		$this->collective = $c;
	}
	

	public function __call($method, array $args) {
		$this->collective->dispatch($this->collectiveAgent, $method, $args);
	}
}

