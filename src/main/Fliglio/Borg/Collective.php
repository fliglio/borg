<?php

namespace Fliglio\Borg;

use Fliglio\Http\RequestReader;

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
	public function getRoutingNamespace() {
		return $this->routing->getNamespace();
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
		$this->drones[get_class($i)] = $i;
	}

	public function mkchan($type = null) {
		return new Chan($type, $this->driver, $this->mapper);
	}

	public function mkChanReader(array $chans) {
		return new ChanReader($this->driver, $this->mapper, $chans);
	}
	
	/**
	 * Call a collective routine
	 */
	public function dispatch(RoutineRequest $req) {
		$r = $this->mapper->marshalRoutineRequest($req);
		$this->driver->go($r);
	}

	/**
	 * Handle a collective routine
	 *
	 * Process HTTP request resulting from a `dispatch`
	 */
	public function mux(RequestReader $r) {
		$req = $this->mapper->unmarshalRoutineRequest($r);
		
		$drone = $this->lookupDrone($req->getType());

		$result = null;
		if ($req->getRetryErrors()) {
			try {
				$result = $drone->handleRequest($req);
			} catch (\Exception $e) {

				error_log("Borg Routine Error: process will be retried");
				throw $e;
			}

		} else {
			try {
				$result = $drone->handleRequest($req);
			} catch (\Exception $e) {

				error_log("Borg Routine Error; process will not be retried");
				error_log($e);
			}
		}
		return $result;
	}

	private function lookupDrone($type) {
		if (!isset($this->drones[$type])) {
			throw new \Exception("drone ".$type." not found");
		}
		return $this->drones[$type];
	}

}
