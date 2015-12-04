<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Chan\ChanFactory;

trait BorgImplant {
	
	private $collective;
	private $availabilityZones = [];
	private $chanFactory;

	// run a routine in the master datacenter
	public function cube() {
		return $this->az($this->collective->getCubeDc());
	}

	// run a routine in your current dataqcenter
	public function coll() {
		return $this->az($this->collective->getDefaultDc());
	}
	
	// only supported for local datacenter usage
	// null type means "Primitive"
	protected function mkchan($type = null) {
		return $this->coll()->mkchan($type);
	}

	private function az($name) {
		if (!isset($this->availabilityZones[$name])) {
			$this->availabilityZones[$name] = new CollectiveWrapper($this, $this->collective, $name);
		}
		return $this->availabilityZones[$name];
	}

	public function setCollective(Collective $c) {
		$this->collective = $c;
	}
}
