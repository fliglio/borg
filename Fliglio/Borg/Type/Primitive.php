<?php

namespace Fliglio\Borg\Type;

use Fliglio\Web\MappableApi;
use Fliglio\Web\MappableApiTrait;

class Primitive implements MappableApi {
	use MappableApiTrait;
	
	public function __construct($val) {
		$this->val = $val;
	}

	public function value() {
		return $this->val;
	}

}
