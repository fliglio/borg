<?php

namespace Fliglio\Borg\Api;

use Fliglio\Web\MappableApi;
use Fliglio\Web\MappableApiTrait;

class Foo implements MappableApi {
	use MappableApiTrait;

	private $msg;

	public function __construct($msg = null) {
		$this->msg = $msg;
	}

	public function getMessage() {
		return $this->msg;
	}

}
