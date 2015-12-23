<?php

namespace Fliglio\Borg;

trait BorgImplant {
	
	private $collective;
	private $availabilityZones = [];
	private $chanFactory;

	/**
	 * run a routine in the master datacenter
	 */
	protected function master() {
		return $this->az($this->collective->getMasterRoutingKey());
	}

	/**
	 * run a routine in your current datacenter
	 */
	protected function coll() {
		return $this->az($this->collective->getLocalRoutingKey());
	}

	/**
	 * Create a new Chan
	 *
	 * - only supported for local datacenter usage
	 * - null type means a primitive (e.g. scalar or array)
	 */
	protected function mkchan($type = null) {
		return $this->coll()->mkchan($type);
	}

	/**
	 * Provide instance of collective to use
	 * (set by the framework, don't use directly)
	 */
	public function setCollective(Collective $c) {
		$this->collective = $c;
	}
	
	private function az($name) {
		if (!isset($this->availabilityZones[$name])) {
			$this->availabilityZones[$name] = new CollectiveWrapper($this, $this->collective, $name);
		}
		return $this->availabilityZones[$name];
	}
}
