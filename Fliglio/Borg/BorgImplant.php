<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Chan\ChanFactory;

trait BorgImplant {
	
	private $collective;
	private $availabilityZones = [];
	private $chanFactory;

	public function coll() {
		return $this->az(CollectiveWrapper::DEFAULT_AZ);
	}

	protected function mkchan($type) {
		return $this->coll()->mkchan($type);
	}

	public function az($name) {
		if (!isset($this->availabilityZones[$name])) {
			$this->availabilityZones[$name] = new CollectiveWrapper($this, $this->collective);
		}
		return $this->availabilityZones[$name];
	}

	public function setCollective(Collective $c) {
		$this->collective = $c;
	}
}
