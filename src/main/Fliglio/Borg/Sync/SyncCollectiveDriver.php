<?php


namespace Fliglio\Borg\Sync;


use Fliglio\Borg\Collective;
use Fliglio\Borg\Driver\CollectiveDriver;
use Fliglio\Http\RequestReader;
use PhpAmqpLib\Message\AMQPMessage;

class SyncCollectiveDriver implements CollectiveDriver {

	private $collective;

	public function setCollective(Collective $collective) {
		$this->collective = $collective;
		return $this;
	}


	public function createChan($id = null) {
		// TODO: Implement createChan() method.
	}

	public function go(RequestReader $req) {
		$this->collective->mux($req);
	}

	public function close() {
		// n/a
	}
}