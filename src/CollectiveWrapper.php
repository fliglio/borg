<?php

namespace Fliglio\Borg;

class CollectiveWrapper {

	private $drone;
	private $collective;
	private $dc;
	private $retryErrors;
	
	/**
	 * Builder Methods
	 */
	public function drone($drone) {
		$this->drone = $drone;
		return $this;
	}
	public function collective($c) {
		$this->collective = $c;
		return $this;
	}
	public function dc($dc) {
		$this->dc = $dc;
		return $this;
	}
	public function retryErrors($r = true) {
		$this->retryErrors = $r;
		return $this;
	}

	/**
	 * Decorate mkchan call to Collective; ensure only local DC usage
	 */
	public function mkchan($type = null) {
		if ($this->dc != RoutingConfiguration::DEFAULT_ROUTING_KEY) {
			throw new \Exception("Making Chans outside of your local datacenter isn't supported");
		}
		return $this->collective->mkchan($type);
	}

	/**
	 * Decorate mkChanReader call to Collective; ensure only local DC usage
	 */
	public function mkChanReader(array $chans) {
		if ($this->dc != RoutingConfiguration::DEFAULT_ROUTING_KEY) {
			throw new \Exception("Making ChanReaders outside of your local datacenter isn't supported");
		}
		return $this->collective->mkChanReader($chans);
	}

	/**
	 * Decorate mkWaitGroup call to Collective; ensure only local DC usage
	 */
	public function mkWaitGroup(array $chans) {
		if ($this->dc != RoutingConfiguration::DEFAULT_ROUTING_KEY) {
			throw new \Exception("Syncing Collective Routines outside of your local datacenter isn't supported");
		}
		return $this->collective->mkWaitGroup($chans);
	}

	/**
	 * Dispatch a Collective Routine async request
	 * use the magic __call method to capture the desired method
	 */
	public function __call($method, array $args) {
		$req = (new RoutineRequestBuilder())
			->ns($this->collective->getRoutingNamespace())
			->dc($this->dc)
			->type(get_class($this->drone))
			->method($method)
			->args($args)
			->retryErrors($this->retryErrors)
			->build();
			
		$this->collective->dispatch($req);
		return;
	}
	
}