<?php

namespace Fliglio\Borg\Type;

use Fliglio\Borg\Chan\Chan;

class TypeUtil {

	const MAPPABLE_API_IFACE = 'Fliglio\Web\MappableApi';

	// $e is a class name or instance
	public static function implementsMappableApi($e) {
		return in_array(self::MAPPABLE_API_IFACE, class_implements($e));
	}

	public static function isMarshallableType($type) {
		switch (true) {
		case is_null($type):
		case self::implementsMappableApi($type):
		case $type == Chan::CLASSNAME:
			return true;
		}
		
		return false;
	}
	
	public static function isA($entity, $type) {
		if (is_null($type)) {
			if (is_object($entity)) {
				return false;
			}
		} else if (!is_object($entity) || !is_a($entity, $type)) {
			return false;
		}
		return true;
	}

	public static function getTypesForMethod($inst, $method) {
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
