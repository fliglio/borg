<?php

namespace Fliglio\Borg\Type;

use Fliglio\Web\ApiMapper;

class ScalarApiMapper implements ApiMapper {

	public function marshal($entity) {
		return $entity->value();
	}

	public function unmarshal($val) {
		return new Scalar($val);
	}
}
