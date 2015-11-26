<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Chan\ChanFactory;

trait BorgImplant {

	private $collective;
	private $chanFactory;

	protected function coll() {
		return $this->collective;
	}

	protected function mkchan($type) {
		return $this->chanFactory->mkchan($type);
	}

	public function setCollective(Collective $c) {
		$c->setInstance($this);
		$this->collective = $c;
	}
	public function setChanFactory(ChanFactory $c) {
		$this->chanFactory = $c;
	}
}
