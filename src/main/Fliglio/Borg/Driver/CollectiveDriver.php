<?php

namespace Fliglio\Borg\Driver;

use Fliglio\Http\RequestReader;

/**
 * Interface for Collective async routines implementation
 * (such as with rabbitmq)
 */
interface CollectiveDriver {
	public function createChan($id = null);
	public function go(RequestReader $req);
	public function close();
}
