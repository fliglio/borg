<?php

namespace Fliglio\Borg;

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
