<?php

namespace Fliglio\Borg;

class CollectiveInvoker {


	public function dispatchRequest(CollectiveDriver $driver, $inst, $method, $body) {
	

		$rMethod = self::getReflectionMethod($inst, $method);
		
		$args = ArgParser::unmarshalArgs(
			$driver,
			self::getTypesFromReflectionMethod($rMethod),
			$body
		);

		return $rMethod->invokeArgs($inst, $args);
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