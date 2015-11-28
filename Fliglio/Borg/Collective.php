<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;

use Fliglio\Http\RequestReader;

class Collective {
	const DEFAULT_AZ = "default";
	const DEFAULT_NS = "default";

	private $agents = [];
	private $driver;

	private $svcNs;
	private $additionalNs;

	public function __construct(CollectiveDriver $driver, $svcNs, $additionalNs = self::DEFAULT_NS) {
		$this->driver = $driver;
		$this->svcNs = $svcNs;
		$this->additionalNs = $additionalNs;
	}

	/**
	 * Keep a copy of instance being assimilated and register the collective & chanfactory on the instance
	 */
	public function assimilate($i) {
		$i->setCollective($this);
		$this->agents[] = $i;
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
	public function dispatch($collectiveAgent, $method, array $args, $dc) {

		$data = [];

		foreach ($args as $arg) {
			if (in_array('Fliglio\Web\MappableApi', class_implements($arg))) {
				$data[] = $arg->marshal();
			} else if ($arg instanceof Chan) {
				$data[] = $arg->getId();
			} else {
				throw new \Exception($entityType . " can't be marshalled");
			}
		}

		$className = get_class($collectiveAgent);
		$topicClass = str_replace("\\", ".", $className);
		$topic = $this->svcNs . '.' . $this->additionalNs . '.' . $topicClass . '.' . $method;
		$this->driver->go($topic, $data);
	}

	/**
	 * Handle incoming request resulting from a `dispatch`
	 */
	public function mux(RequestReader $r) {
		if (!$r->isHeaderSet("X-routing_key")) {
			throw new \Exception("x-routing_key not set");
		}
		$topic = $r->getHeader("X-routing_key");
		$parts = explode(".", $topic);

		$method = array_pop($parts);
		array_shift($parts);
		array_shift($parts);
		$type = implode("\\", $parts);

		$inst = $this->getCollectiveAgent($type);


		$rMethod = $this->getReflectionMethod($inst, $method);
		$args = $this->getMethodArgs($rMethod, $r->getBody());

		return $rMethod->invokeArgs($inst, $args);

	}

	private function getCollectiveAgent($type) {
		foreach ($this->agents as $agent) {
			if ($type == get_class($agent)) {
				return $agent;
			}
		}
		throw new \Exception("agent ".$type."not found");
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

		$argArr = json_decode($body);
		$params = $rMethod->getParameters();

		for ($i = 0; $i < count($argArr); $i++) {

			$type = $params[$i]->getClass()->getName();

			if (in_array('Fliglio\Web\MappableApi', class_implements($type))) {
				$argEntities[] = $type::unmarshal($argArr[$i]);
			} else if ($type == Chan::CLASSNAME) {
				$argEntities[] = new Chan($type, $this->driver, $argArr[$i]);
			} else {
				throw new \Exception($type . " can't be unmarshalled");
			}

		}

		return $argEntities;
	}
}
