<?php

namespace Fliglio\Borg;

use Fliglio\Http\RequestReader;
use Fliglio\Borg\Type\ArgMapper;
use Fliglio\Borg\Driver\CollectiveDriver;
use Fliglio\Borg\Driver\WireMapper;

class Collective {
	private $driver;
	private $mapper;
	private $routing;

	private $drones = [];

	public function __construct(CollectiveDriver $driver, WireMapper $mapper, RoutingConfiguration $routing) {
		$this->driver = $driver;
		$this->mapper = $mapper;
		$this->routing = $routing;
	}

	/**
	 * Get routing_key component to route to datcenter this is running in
	 */
	public function getLocalRoutingKey() {
		return $this->routing->getLocalRoutingKey();
	}

	/**
	 * Get routing_key component to route to the master datcenter
	 */
	public function getMasterRoutingKey() {
		return $this->routing->getMasterRoutingKey();
	}

	/**
	 * Keep a copy of instance being assimilated and register the collective & chanfactory on the instance
	 */
	public function assimilate($i) {
		$i->setCollective($this);
		$this->drones[] = $i;
	}

	/**
	 * Create a new Chan and return it
	 */
	public function mkchan($type) {
		return new Chan($type, $this->driver, $this->mapper);
	}

	public function mkChanReader(array $chans) {
		return new ChanReader($this->driver, $this->mapper, $chans);
	}

	/**
	 * Call a collective routine async
	 */
	public function dispatch($drone, $method, array $args, $dc) {
		$topic = new TopicConfiguration($this->routing->getNamespace(), $dc, $drone, $method);
		$vos = $this->mapper->marshalForMethod($args, $drone, $method);
	
		$this->driver->go($topic->getTopicString(), $vos);
	}

	/**
	 * Handle a collective routing
	 *
	 * Process HTTP request resulting from a `dispatch`
	 */
	public function mux(RequestReader $r) {
		if (!$r->isHeaderSet("X-routing-key")) {
			throw new \Exception("x-routing-key not set");
		}

		$topic = TopicConfiguration::fromTopicString($r->getHeader("X-routing-key"));
		$inst = $this->lookupDrone($topic->getType());
		$vos = json_decode($r->getBody(), true);
		
		$entities = $this->mapper->unmarshalForMethod($vos, $inst, $topic->getMethod());
		return call_user_func_array([$inst, $topic->getMethod()], $entities);
	}

	private function lookupDrone($type) {
		foreach ($this->drones as $drone) {
			if ($drone instanceof $type) {
				return $drone;
			}
		}
		throw new \Exception("drone ".$type." not found");
	}

}
