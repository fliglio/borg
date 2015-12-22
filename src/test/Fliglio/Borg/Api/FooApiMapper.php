<?php

namespace Fliglio\Borg\Api;

use Fliglio\Web\ApiMapper;

class FooApiMapper implements ApiMapper {

	public function marshal($entity) {
		return [
			"message" => $entity->getMessage(),
		];
	}

	public function unmarshal($vo) {
		return new Foo(isset($vo['message']) ? $vo['message'] : null);
	}
}
