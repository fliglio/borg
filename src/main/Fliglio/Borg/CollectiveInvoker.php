<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Type\TypeUtil;
use Fliglio\Borg\Type\ArgMapper;

class CollectiveInvoker {

	private $driver;

	public function __construct(CollectiveDriver $driver) {
		$this->driver = $driver;
	}

	public function sendRequest($topic, $args) {
		$data = ArgMapper::marshalArgs($args);
		$this->driver->go($topic->getTopicString(), $data);
	}

	/**
	 * Make the collective async routine call on the specified drone
	 *
	 * Unmarshal an http request body into an array of args and call
	 * the specified method on the specified drone with those args.
	 */
	public function handleRequest($inst, $method, array $vos) {
		
		$args = ArgMapper::unmarshalArgs(
			$this->driver,
			TypeUtil::getTypesForMethod($inst, $method),
			$vos
		);
	
		return call_user_func_array([$inst, $method], $args);
	}
}
