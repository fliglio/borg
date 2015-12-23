<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Type\TypeUtil;
use Fliglio\Borg\Type\ArgMapper;

class CollectiveInvoker {

	private $driver;

	public function __construct(CollectiveDriver $driver) {
		$this->driver = $driver;
	}

	public function marshal($args, $inst, $method) {
		return ArgMapper::marshalForMethod($args, $inst, $method);
	}

	public function unmarshal(array $vos, $inst, $method) {
		return ArgMapper::unmarshalForMethod($this->driver, $vos, $inst, $method);
	}
}
