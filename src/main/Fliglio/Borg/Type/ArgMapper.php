<?php

namespace Fliglio\Borg\Type;

use Fliglio\Borg\CollectiveDriver;
use Fliglio\Borg\Chan\Chan;
use Fliglio\Borg\Type\Primitive;

class ArgMapper {

	public static function marshalArgs(array $args) {
		$data = [];

		foreach ($args as $arg) {
			$data[] = self::marshalArg($arg);
		}
		return $data;
	}

	public static function marshalArg($arg) {
		// wrap primitive
		if (!is_object($arg)) {
			$arg = new Primitive($arg);
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


	public static function unmarshalArgs(CollectiveDriver $driver, array $types, array $args) {
		if (count($types) < count($args)) {
			throw new \Exception("too many args for method signature.");
		}

		$argEntities = [];
		for ($i = 0; $i < count($args); $i++) {
			$argEntities[] = self::unmarshalArg($driver, $types[$i], $args[$i]);
		}

		return $argEntities;
	}

	public static function unmarshalArg(CollectiveDriver $driver, $type, $arg) {
		// Primitive without a hint is expected
		if (is_null($type)) {
			$t = Primitive::getClass();
			$p = $t::unmarshal($arg);
			return $p->value();
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
