<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;

use Fliglio\Http\RequestReader;

class Collective {
	const DEFAULT_DC = "default";

	private $drones = [];
	private $driver;

	private $svcNs;
	private $cubeDc;
	private $defaultDc;

	private $invoker;

	public function __construct(CollectiveDriver $driver, $svcNs, $cubeDc, $defaultDc = self::DEFAULT_DC) {
		$this->driver = $driver;
		$this->svcNs = $svcNs;
		$this->cubeDc = $cubeDc;
		$this->defaultDc = $defaultDc;

		$this->invoker = new CollectiveInvoker();
	}

	public function getDefaultDc() {
		return $this->defaultDc;
	}
	public function getCubeDc() {
		return $this->cubeDc;
	}

	/**
	 * Keep a copy of instance being assimilated and register the collective & chanfactory on the instance
	 */
	public function assimilate($i) {
		$i->setCollective($this);
		$this->drones[] = $i;
	}

	private function lookupDrone($type) {
		foreach ($this->drones as $drone) {
			if ($type == get_class($drone)) {
				return $drone;
			}
		}
		throw new \Exception("drone ".$type." not found");
	}

	/**
	 * Create a new Chan and return it
	 */
	public function mkchan($type, $dc) {
		return new Chan($type, $this->driver);
	}

	/**
	 * Dispatch a new call
	 */
	public function dispatch($collectiveDrone, $method, array $args, $dc) {
		$data = ArgParser::marshalArgs($args);
		
		$topic = new Topic($this->svcNs, $dc, $collectiveDrone, $method);
		$this->driver->go((string)$topic, $data);
	}

	/**
	 * Handle incoming request resulting from a `dispatch`
	 */
	public function mux(RequestReader $r) {
		if (!$r->isHeaderSet("X-routing-key")) {
			throw new \Exception("x-routing-key not set");
		}

		$topic = Topic::fromString($r->getHeader("X-routing-key"));
		$inst = $this->lookupDrone($topic->getType());
	
		return $this->invoker->dispatchRequest($this->driver, $inst, $topic->getMethod(), json_decode($r->getBody(), true));
	}

}
