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
		$types = TypeUtil::getTypesForMethod($inst, $method);
		return  ArgMapper::marshalArgs($args, $types);
	}

	public function unmarshal(array $vos, $inst, $method) {
		$types = TypeUtil::getTypesForMethod($inst, $method);

		return ArgMapper::unmarshalArgs($this->driver, $types, $vos);
	}
}
