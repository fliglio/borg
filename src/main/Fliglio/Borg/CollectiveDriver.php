<?php

namespace Fliglio\Borg;

/**
 * Interface for Collective async routines implementation
 * (such as with rabbitmq)
 */
interface CollectiveDriver {
	public function createChan($id = null);
	public function go($topic, array $data);
	public function close();
}
