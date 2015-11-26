<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Borg\MessagingDriver;

class ChanFactory {

	private $driver;

	public function __construct(MessagingDriver $driver) {
		$this->driver = $driver;
	}

	public function mkchan($type) {
		return new Chan($type, $driver);
	}
}

