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

		$this->invoker = new CollectiveInvoker($this->driver);
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

	/**
	 * Create a new Chan and return it
	 */
	public function mkchan($type, $dc) {
		return new Chan($type, $this->driver);
	}

	/**
	 * Dispatch a new call
	 */
	public function dispatch($drone, $method, array $args, $dc) {
		$topic = new TopicConfiguration($this->svcNs, $dc, $drone, $method);
		$vos = $this->invoker->marshal($args, $drone, $method);
	
		$this->driver->go($topic->getTopicString(), $vos);
	}

	/**
	 * Handle incoming request resulting from a `dispatch`
	 */
	public function mux(RequestReader $r) {
		if (!$r->isHeaderSet("X-routing-key")) {
			throw new \Exception("x-routing-key not set");
		}

		$topic = TopicConfiguration::fromTopicString($r->getHeader("X-routing-key"));
		$inst = $this->lookupDrone($topic->getType());
		$vos = json_decode($r->getBody(), true);
		
		$entities = $this->invoker->unmarshal($vos, $inst, $topic->getMethod());
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
