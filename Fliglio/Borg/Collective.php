<?php

namespace Fliglio\Borg;


class Collective {

	private $inst;
	private $driver;

	public function __construct(MessagingDriver $driver) {
		$this->driver = $driver;
	}
	
	public function setInstance($i) {
		$this->inst = $i;
	}
	
	public function getInstance() {
		return $this->inst;
	}

	public function __call($method, array $args) {

		$data = [];

		foreach ($args as $arg) {
			if (!in_array('Fliglio\Web\MappableApi', class_implements($arg))) {
				throw new \Exception($entityType . " doesn't implement Fliglio\Web\MappableApi");
			}
			$data[] = $arg->marshal();
		}

		$className = get_class($this->inst);
		$topicBase = str_replace("\\", ".", $className);
		$this->driver->go($topicBase, $method, $data);
	}
}
