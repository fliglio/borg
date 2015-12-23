<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Type\TypeUtil;
use Fliglio\Borg\Type\ArgMapper;

class CollectiveInvoker {

	private $driver;

	public function __construct(CollectiveDriver $driver) {
		$this->driver = $driver;
	}

	public function sendRequest($topic, $args, $inst, $method) {
		$types = TypeUtil::getTypesForMethod($inst, $method);
		$vos = ArgMapper::marshalArgs($args, $types);
		$this->driver->go($topic->getTopicString(), $vos);
	}

	/**
	 * Make the collective async routine call on the specified drone
	 *
	 * Unmarshal an http request body into an array of args and call
	 * the specified method on the specified drone with those args.
	 */
	public function handleRequest($inst, $method, array $vos) {
		$types = TypeUtil::getTypesForMethod($inst, $method);

		$entities = ArgMapper::unmarshalArgs($this->driver, $types, $vos);
	
		return call_user_func_array([$inst, $method], $entities);
	}
}
