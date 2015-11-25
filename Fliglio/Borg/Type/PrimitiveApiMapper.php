<?php

namespace Fliglio\Borg\Type;

use Fliglio\Web\ApiMapper;

class PrimitiveApiMapper implements ApiMapper {

	public function marshal($entity) {
		return $entity->value();
	}

	public function unmarshal($val) {
		return new Primitive($val);
	}
}
