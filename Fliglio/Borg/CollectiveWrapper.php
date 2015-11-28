<?php

namespace Fliglio\Borg;


class CollectiveWrapper {

	const DEFAULT_AZ = "default";

	private $collectiveAgent;
	private $collective;
	private $dc;

	public function __construct($agent, Collective $c, $dc = self::DEFAULT_AZ) {
		$this->collectiveAgent = $agent;
		$this->collective = $c;
		$this->dc = $dc;
	}
	
	public function mkchan($type) {
		return $this->collective->mkchan($type);
	}

	public function __call($method, array $args) {
		$this->collective->dispatch($this->collectiveAgent, $method, $args);
	}
}

