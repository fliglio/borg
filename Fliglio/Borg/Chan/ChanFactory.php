<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Borg\MessagingDriver;

class ChanFactory {

	private $type;
	private $driver;

	public function __construct($type, MessagingDriver $driver) {
		$this->type = $type;
		$this->driver = $driver;
	}


	public function makechan($type) {
		return new Chan($type, $driver->createChanDriver());
	}
}

