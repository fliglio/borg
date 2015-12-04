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

	public function __construct(CollectiveDriver $driver, $svcNs, $cubeDc, $defaultDc = self::DEFAULT_DC) {
		$this->driver = $driver;
		$this->svcNs = $svcNs;
		$this->cubeDc = $cubeDc;
		$this->defaultDc = $defaultDc;
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
	 * @todo impl az
	 */
	public function mkchan($type, $dc) {
		return new Chan($type, $this->driver);
	}

	/**
	 * Dispatch a new call
	 * @todo impl az
	 */
	public function dispatch($collectiveDrone, $method, array $args, $dc) {
		$data = ArgParser::marshalArgs($args);

		$className = get_class($collectiveDrone);
		$topicClass = str_replace("\\", ".", $className);
		$topic = $this->svcNs . '.' . $dc . '.' . $topicClass . '.' . $method;
		$this->driver->go($topic, $data);
	}
	

	/**
	 * Handle incoming request resulting from a `dispatch`
	 */
	public function mux(RequestReader $r) {
		if (!$r->isHeaderSet("X-routing-key")) {
			throw new \Exception("x-routing-key not set");
		}

		$topic = $r->getHeader("X-routing-key");

		list($type, $method) = self::parseTopic($topic);

		$inst = $this->lookupDrone($type);

		$rMethod = self::getReflectionMethod($inst, $method);
		
		$args = ArgParser::unmarshalArgs(
			$this->driver, 
			self::getTypesFromReflectionMethod($rMethod),
			json_decode($r->getBody(), true)
		);

		return $rMethod->invokeArgs($inst, $args);
	}

	private function parseTopic($topic) {
		$parts = explode(".", $topic);

		$method = array_pop($parts);
		array_shift($parts);
		array_shift($parts);
		$type = implode("\\", $parts);

		return [$type, $method];
	}

	private function lookupDrone($type) {
		foreach ($this->drones as $drone) {
			if ($type == get_class($drone)) {
				return $drone;
			}
		}
		throw new \Exception("drone ".$type."not found");
	}

	private static function getReflectionMethod($className, $methodName) {
		try {
			return new \ReflectionMethod($className, $methodName);
		} catch (\ReflectionException $e) {
			$rClass = new \ReflectionClass($className);
			$parentRClass = $rClass->getParentClass();
			if (!is_object($parentRClass)) {
				throw new \Exception("Method '{$methodName}' does not exist");
			}
			$parentClassName = $parentRClass->getName();
			return self::getReflectionMethod($parentClassName, $methodName);
		}
	}

	private static function getTypesFromReflectionMethod(\ReflectionMethod $rMethod) {
		$params = $rMethod->getParameters();

		$types = [];
		foreach ($params as $param) {
			$cl = $param->getClass();
			
			if (is_null($cl)) {
				$types[] = null;
			} else {
				$types[] = $cl->getName();
			}
		}

		return $types;
	}
}
