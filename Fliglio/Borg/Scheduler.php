<?php

namespace Fliglio\Borg;


class Scheduler {

	private $type;
	private $driver;

	public function __construct($type, MessagingDriver $driver) {
		$this->type = $type;
		$this->driver = $driver;
	}


	public function __call($method, array $args) {

		$data = [];

		foreach ($args as $arg) {
			if (!in_array('Fliglio\Web\MappableApi', class_implements($arg))) {
				throw new \Exception($entityType . " doesn't implement Fliglio\Web\MappableApi");
			}
			$data[] = $arg->marshal();
		}

		$this->driver->go($this->type, $method, $data);
	}
}
