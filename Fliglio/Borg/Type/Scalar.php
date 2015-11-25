<?php

namespace Fliglio\Borg\Type;

use Fliglio\Web\MappableApi;
use Fliglio\Web\MappableApiTrait;

class Scalar implements MappableApi {
	use MappableApiTrait;
	
	public function __cosntruct($val) {
		$this->val = $val;
	}

	public function value() {
		return $this->val;
	}

}
