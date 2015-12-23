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
		if ($this->dc != RoutingConfiguration::DEFAULT_ROUTING_KEY) {
			throw new \Exception("Making Chans outside of your local datacenter isn't supported");
		}
		return $this->collective->mkchan($type);
	}

	public function mkChanReader(array $chans) {
		if ($this->dc != RoutingConfiguration::DEFAULT_ROUTING_KEY) {
			throw new \Exception("Making ChanReaders outside of your local datacenter isn't supported");
		}
		return $this->collective->mkChanReader($chans);
	}


	/**
	 * Using the magic __call method to capture the desired method to call, 
	 * dispatch a Collective async routine request
	 */
	public function __call($method, array $args) {
		$this->collective->dispatch($this->drone, $method, $args, $this->dc);
	}
}

