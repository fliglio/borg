<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;

use Fliglio\Http\RequestReader;

class Collective {
	const DEFAULT_DC = "default";

	private $agents = [];
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
		$this->agents[] = $i;
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
	public function dispatch($collectiveAgent, $method, array $args, $dc) {
		$data = self::marshalArgs($args);

		$className = get_class($collectiveAgent);
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
	
		if (in_array('Fliglio\Web\MappableApi', class_implements($arg))) {
			return $arg->marshal();
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

		$argArr = json_decode($body, true);
		$params = $rMethod->getParameters();

		for ($i = 0; $i < count($argArr); $i++) {

			$type = $params[$i]->getClass()->getName();

			if (in_array('Fliglio\Web\MappableApi', class_implements($type))) {
				$argEntities[] = $type::unmarshal($argArr[$i]);
			} else if ($type == Chan::CLASSNAME) {
				$argEntities[] = new Chan($argArr[$i]["type"], $this->driver, $argArr[$i]["id"]);
			} else {
				throw new \Exception($type . " can't be unmarshalled");
			}

		}

		return $argEntities;
	}
}
