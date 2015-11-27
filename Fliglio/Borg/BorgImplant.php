<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Chan\ChanFactory;

trait BorgImplant {

	private $collectiveWrapper;
	private $chanFactory;

	public function coll() {
		return $this->collectiveWrapper;
	}

	protected function mkchan($type) {
		return $this->chanFactory->mkchan($type);
	}

	public function setCollective(Collective $c) {
		$this->collectiveWrapper = new CollectiveWrapper($this, $c);
	}
	public function setChanFactory(ChanFactory $c) {
		$this->chanFactory = $c;
	}
}
