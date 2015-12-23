<?php

namespace Fliglio\Borg\Mapper;

use Fliglio\Borg\Driver\CollectiveDriver;
use Fliglio\Borg\Chan;
use Fliglio\Borg\Driver\WireMapper;

class DefaultMapper implements WireMapper {
	
	const MAPPABLE_API_IFACE = 'Fliglio\Web\MappableApi';
	
	private $driver;

	public function __construct(CollectiveDriver $driver) {
		$this->driver = $driver;
	}

	public function marshalForMethod(array $args, $inst, $method) {
		$types = self::getTypesForMethod($inst, $method);
		$data = [];

		for ($i = 0; $i < count($args); $i++) {
			$type = $types[$i];
			$arg = $args[$i];
			$data[] = $this->marshalArg($arg, $type);
		}
		return $data;
	}

	public function unmarshalForMethod(array $vos, $inst, $method) {
		$types = self::getTypesForMethod($inst, $method);

		if (count($types) < count($vos)) {
			throw new \Exception("too many args for method signature.");
		}

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
