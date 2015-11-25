<?php

namespace Fliglio\Scheduler;


class Scheduler {

	private $type;
	private $driver;

	public function __construct($type, MessagingDriver $driver) {
		$this->type = $type;
		$this->driver = $driver;
	}


	public function makechan($type) {
		return new Chan($type, $driver->createChanDriver());
	}

	public function makeChanReader() {
		return new ChanReader($this->driver);
	}

	public function __call($method, array $args) {
		
	}
}
