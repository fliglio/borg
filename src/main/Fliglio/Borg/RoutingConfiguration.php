<?php

namespace Fliglio\Borg;

/**
 * Configuration for how Collective Routines are routed
 *
 * - namespace: namespace for messaging topic so that this services routines
 *   don't get crossed with anothers
 * - localRoutingKey: component for messaging topic to signal to route
 *   Collective Routine requests to other instances of this service in the
 *   current datacenter
 * - masterRoutingKey: component for messaging topic to signal to route
 *   CollectiveRoutine requests to instances of this service in the master
 *   datacenter
 */
class RoutingConfiguration {
	
	const DEFAULT_ROUTING_KEY = 'default';

	private $ns;
	private $localRoutingKey;
	private $masterRoutingKey;

	public function __construct($ns, $localRoutingKey = self::DEFAULT_ROUTING_KEY, $masterRoutingKey = self::DEFAULT_ROUTING_KEY) {
		$this->ns = $ns;
		$this->localRoutingKey = $localRoutingKey;
		$this->masterRoutingKey = $masterRoutingKey;
	}

	public function getNamespace() {
		return $this->ns;
	}
	public function getLocalRoutingKey() {
		return $this->localRoutingKey;
	}
	public function getMasterRoutingKey() {
		return $this->masterROutingKey;
	}

}
