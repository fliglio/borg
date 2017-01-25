<?php

namespace Fliglio\Borg\Mapper;

use Fliglio\Borg\Driver\CollectiveDriver;
use Fliglio\Borg\Chan;
use Fliglio\Borg\Driver\WireMapper;
use Fliglio\Http\RequestReader;
use Fliglio\Borg\RoutineRequest;
use Fliglio\Borg\RoutineRequestBuilder;
use Fliglio\Flfc\Request;

class DefaultMapper implements WireMapper {
	
	const MAPPABLE_API_IFACE = 'Fliglio\Web\MappableApi';
	
	private $driver;

	public function __construct(CollectiveDriver $driver) {
		$this->driver = $driver;
	}
	public function marshalRoutineRequest(RoutineRequest $req) {
		$type = $req->getType();
		$method = $req->getMethod();

		$vos = $this->marshalForMethod(
			$req->getArgs(),
			$req->getType(),
			$req->getMethod()
		);
		$vos[] = $req->getRetryErrors();

		$topicStr = $this->marshalTopicString($req->getNs(), $req->getDc(), $req->getType(), $req->getMethod());

		$r = new Request();
		$r->addHeader('X-routing-key', $topicStr);
		$r->setBody(json_encode($vos));
		
		return $r;
	}

	public function unmarshalRoutineRequest(RequestReader $r) {
		if (!$r->isHeaderSet("X-routing-key")) {
			throw new \Exception("x-routing-key not set");
		}
		list($ns, $dc, $type, $method) = $this->unmarshalTopicString($r->getHeader('X-routing-key'));

		$vos = json_decode($r->getBody(), true);

		$retryErrors = array_pop($vos);
		$entities = $this->unmarshalForMethod($vos, $type, $method);

		return (new RoutineRequestBuilder())
			->ns($ns)
			->dc($dc)
			->type($type)
			->method($method)
			->args($entities)
			->retryErrors($retryErrors)
			->build();
	}

	private function marshalTopicString($ns, $dc, $type, $method) {
		$topicClass = str_replace("\\", ".", $type);
		return $ns . '.' . $dc . '.' . $topicClass . '.' . $method;
	}
	private function unmarshalTopicString($str) {
		$parts = explode(".", $str);

		$method = array_pop($parts);
		$ns = array_shift($parts);
		$dc = array_shift($parts);
		$type = implode("\\", $parts);

		return [$ns, $dc, $type, $method];
	}

	private function marshalForMethod(array $args, $inst, $method) {
		list($types, $optionalArgs) = self::getTypesForMethod($inst, $method);
		self::validateParameterCount(count($args), count($types), $optionalArgs);
		
		$data = [];
		for ($i = 0; $i < count($args); $i++) {
			$type = $types[$i];
			$arg = $args[$i];
			$data[] = $this->marshalArg($arg, $type);
		}
		return $data;
	}

	private function unmarshalForMethod(array $vos, $inst, $method) {
		list($types, $optionalArgs) = self::getTypesForMethod($inst, $method);
		self::validateParameterCount(count($vos), count($types), $optionalArgs);

		$argEntities = [];
		for ($i = 0; $i < count($vos); $i++) {
			$argEntities[] = $this->unmarshalArg($vos[$i], $types[$i]);
		}

		return $argEntities;
	}

	public function marshalArg($arg, $type) {
		if (!self::isMarshallableType($type)) {
			throw new \Exception(sprintf("Type '%s' isn't marshallable", $type));
		}
		// always allow null
		if (is_null($arg)) {
			return null;
		}

		if (!self::isA($arg, $type)) {
			throw new \Exception(sprintf("cannot marshal entity with %s", $type));
		}

		// wrap primitive
		if (!is_object($arg)) {
			return $arg;
		}
		
		// object of type MappableApi
		if (self::implementsMappableApi($arg)) {
			return $arg->marshal();

		// object of type Chan
		} else if ($arg instanceof Chan) {
			return ["type" => $arg->getType(), "id" => $arg->getId()];
		}
		throw new \Exception(sprintf("arg '%s' can't be marshalled", print_r($arg, true)));
	}

	public function unmarshalArg($arg, $type) {
		if (!self::isMarshallableType($type)) {
			throw new \Exception(sprintf("Type '%s' isn't marshallable", $type));
		}
		
		// always allow null
		if (is_null($arg)) {
			return null;
		}

		// Primitive without a hint is expected
		if (is_null($type)) {
			return $arg;
		}

		// MappableApi
		if (self::implementsMappableApi($type)) {
			return $type::unmarshal($arg);

		// Chan
		} else if ($type == Chan::CLASSNAME) {
			return new Chan($arg["type"], $this->driver, $this, $arg["id"]);
		}

		throw new \Exception($type . " can't be unmarshalled");
	}

	private static function validateParameterCount($numArgs, $numTypes, $numOpt) {
		if (($numTypes < $numArgs) || (($numTypes - $numOpt) > $numArgs)) {
			throw new \Exception("Wrong number of args for method signature.");
		}
	}

	// $e is a class name or instance
	private static function implementsMappableApi($e) {
		return in_array(self::MAPPABLE_API_IFACE, class_implements($e));
	}

	private static function isMarshallableType($type) {
		switch (true) {
		case is_null($type):
		case self::implementsMappableApi($type):
		case $type == Chan::CLASSNAME:
			return true;
		}
		
		return false;
	}
	
	private static function isA($entity, $type) {
		if (is_null($type)) {
			if (is_object($entity)) {
				return false;
			}
		} else if (!is_object($entity) || !is_a($entity, $type)) {
			return false;
		}
		return true;
	}

	private static function getTypesForMethod($inst, $method) {
		$rMethod = new \ReflectionMethod($inst, $method);
		
		$params = $rMethod->getParameters();

		$optionalArgs = 0;
		$types = [];
		foreach ($params as $param) {
			if ($param->isOptional()) {
				$optionalArgs++;
			}
			$cl = $param->getClass();
			
			if (is_null($cl)) {
				$types[] = null;
			} else {
				$types[] = $cl->getName();
			}
		}

		return [$types, $optionalArgs];
	}

}
