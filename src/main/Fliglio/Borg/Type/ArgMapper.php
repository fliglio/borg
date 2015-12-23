<?php

namespace Fliglio\Borg\Type;

use Fliglio\Borg\CollectiveDriver;
use Fliglio\Borg\Chan\Chan;

class ArgMapper {

	public static function marshalForMethod($args, $inst, $method) {
		$types = TypeUtil::getTypesForMethod($inst, $method);
		return  self::marshalArgs($args, $types);
	}

	public static function marshalArgs(array $args, array $types) {
		$data = [];

		for ($i = 0; $i < count($args); $i++) {
			$type = $types[$i];
			$arg = $args[$i];
			$data[] = self::marshalArg($arg, $type);
		}
		return $data;
	}

	public static function unmarshalForMethod(CollectiveDriver $driver, array $vos, $inst, $method) {
		$types = TypeUtil::getTypesForMethod($inst, $method);

		return self::unmarshalArgs($driver, $types, $vos);
	}

	public static function unmarshalArgs(CollectiveDriver $driver, array $types, array $args) {
		if (count($types) < count($args)) {
			throw new \Exception("too many args for method signature.");
		}

		$argEntities = [];
		for ($i = 0; $i < count($args); $i++) {
			$argEntities[] = self::unmarshalArg($driver, $args[$i], $types[$i]);
		}

		return $argEntities;
	}

	public static function marshalArg($arg, $type) {
		if (!TypeUtil::isMarshallableType($type)) {
			throw new \Exception(sprintf("Type '%s' isn't marshallable", $type));
		}
		if (!TypeUtil::isA($arg, $type)) {
			throw new \Exception(sprintf("cannot marshal entity with %s", $type));
		}

		// wrap primitive
		if (!is_object($arg)) {
			return $arg;
		}
		
		// object of type MappableApi
		if (TypeUtil::implementsMappableApi($arg)) {
			return $arg->marshal();

		// object of type Chan
		} else if ($arg instanceof Chan) {
			return ["type" => $arg->getType(), "id" => $arg->getId()];
		}
		throw new \Exception(sprintf("arg '%s' can't be marshalled", print_r($arg, true)));
	}

	public static function unmarshalArg(CollectiveDriver $driver, $arg, $type) {
		// Primitive without a hint is expected
		if (is_null($type)) {
			return $arg;
		}

		// MappableApi
		if (TypeUtil::implementsMappableApi($type)) {
			return $type::unmarshal($arg);

		// Chan
		} else if ($type == Chan::CLASSNAME) {
			try {
				return new Chan($arg["type"], $driver, $arg["id"]);
			} catch (\Exception $e) {
				throw new \Exception(print_r($arg, true));
			}
		}

		throw new \Exception($type . " can't be unmarshalled");
	}


}
