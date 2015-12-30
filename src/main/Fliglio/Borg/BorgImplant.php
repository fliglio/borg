<?php

namespace Fliglio\Borg;

trait BorgImplant {
	
	private $collective;

	/**
	 * run a routine in the master datacenter
	 */
	protected function master($retryErrors = false) {
		return $this->defaultBuilder($retryErrors)
			->dc($this->collective->getMasterRoutingKey());
	}

	/**
	 * run a routine in your current datacenter
	 */
	protected function coll($retryErrors = false) {
		return $this->defaultBuilder($retryErrors)
			->dc($this->collective->getLocalRoutingKey());
	}

	/**
	 * Provide instance of collective to use
	 * (set by the framework, don't use directly)
	 */
	public function setCollective(Collective $c) {
		$this->collective = $c;
	}
	
	/**
	 * Handle a Collective Routine request
	 */
	public function handleRequest(RoutineRequest $req) {
		$handler = [$this, $req->getTopic()->getMethod()];

		return call_user_func_array($handler, $req->getArgs());
	}

	private function defaultBuilder($retryErrors) {
		return (new CollectiveWrapper)
			->drone($this)
			->collective($this->collective)
			->retryErrors($retryErrors);
	}
}
