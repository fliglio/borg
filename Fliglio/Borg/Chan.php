<?php

namespace Fliglio\Borg;

class Chan {

	private $type;
	private $driver;

	public function __construct($type, ChanDriver $driver) {
		$this->type = $type;
		$this->driver = $driver;

	}

	public function getId() {
		$this->driver->getId();
	}

	public function send($entity) {
		//MappableApi or scalar value
	}

}
