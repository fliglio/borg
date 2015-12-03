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
		$data = self::marshalArgs($args);

		$className = get_class($collectiveDrone);
		$topicClass = str_replace("\\", ".", $className);
		$topic = $this->svcNs . '.' . $dc . '.' . $topicClass . '.' . $method;
		$this->driver->go($topic, $data);
	}
	
	public static function marshalArgs(array $args) {
		$data = [];

		foreach ($args as $arg) {
			$data[] = self::marshalArg($arg);
		}
		return $data;
	}

	public static function marshalArg($arg) {
	
		// unwrapper primitive
		if (!is_object($arg)) {
			return $arg;

		// object of type MappableApi
		} else if (in_array('Fliglio\Web\MappableApi', class_implements($arg))) {
			return $arg->marshal();

		// object of type Chan
		} else if ($arg instanceof Chan) {
			return ["type" => $arg->getType(), "id" => $arg->getId()];
		}
		throw new \Exception("arg can't be marshalled");
	}


	/**
	 * Handle incoming request resulting from a `dispatch`
	 */
	public function mux(RequestReader $r) {
		if (!$r->isHeaderSet("X-routing-key")) {
			throw new \Exception("x-routing-key not set");
		}
		$topic = $r->getHeader("X-routing-key");
		$parts = explode(".", $topic);

		$method = array_pop($parts);
		array_shift($parts);
		array_shift($parts);
		$type = implode("\\", $parts);

		$inst = $this->getCollectiveDrone($type);


		$rMethod = $this->getReflectionMethod($inst, $method);
		$args = $this->getMethodArgs($rMethod, $r->getBody());

		return $rMethod->invokeArgs($inst, $args);

	}

	private function getCollectiveDrone($type) {
		foreach ($this->drones as $drone) {
			if ($type == get_class($drone)) {
				return $drone;
			}
		}
		throw new \Exception("drone ".$type."not found");
	}

	private function getReflectionMethod($className, $methodName) {
		try {
			return new \ReflectionMethod($className, $methodName);
		} catch (\ReflectionException $e) {
			$rClass = new \ReflectionClass($className);
			$parentRClass = $rClass->getParentClass();
			if (!is_object($parentRClass)) {
				throw new CommandNotFoundException("Method '{$methodName}' does not exist");
			}
			$parentClassName = $parentRClass->getName();
			return self::getReflectionMethod($parentClassName, $methodName);
		}
	}

	private function getMethodArgs(\ReflectionMethod $rMethod, $body) {
		$argEntities = [];

		$argArr = json_decode($body, true);
		$params = $rMethod->getParameters();

		for ($i = 0; $i < count($argArr); $i++) {
			$cl = $params[$i]->getClass();
			
			// handle when a primitive without a hint is expected
			if (is_null($cl)) {
				$argEntities[] = $argArr[$i];

			// handle MappableApi & Chan hints
			} else {
				$type = $cl->getName();

				if (in_array('Fliglio\Web\MappableApi', class_implements($type))) {
					$argEntities[] = $type::unmarshal($argArr[$i]);
				} else if ($type == Chan::CLASSNAME) {
					$argEntities[] = new Chan($argArr[$i]["type"], $this->driver, $argArr[$i]["id"]);
				} else {
					throw new \Exception($type . " can't be unmarshalled");
				}
			}
		}

		return $argEntities;
	}
}
