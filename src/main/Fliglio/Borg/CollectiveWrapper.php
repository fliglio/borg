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
	
	/**
	 * Wrap chan factory and enforce only creating in local dc
	 */
	public function mkchan($type) {
		if ($this->dc != RoutingConfiguration::DEFAULT_ROUTING_KEY) {
			throw new \Exception("Making Chans outside of your local datacenter isn't supported");
		}
		return $this->collective->mkchan($type);
	}

	/**
	 * Wrap chan reader factory and enforce only creating in local dc
	 */
	public function mkChanReader(array $chans) {
		if ($this->dc != RoutingConfiguration::DEFAULT_ROUTING_KEY) {
			throw new \Exception("Making ChanReaders outside of your local datacenter isn't supported");
		}
		return $this->collective->mkChanReader($chans);
	}


	/**
	 * Dispatch a Collective Routine async request
	 * use the magic __call method to capture the desired method
	 */
	public function __call($method, array $args) {
		$this->collective->dispatch($this->drone, $method, $args, $this->dc);
	}
}

