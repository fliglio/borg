<?php

namespace Fliglio\Borg;

use Fliglio\Web\MappableApi;

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

	public function push(MappableApi $entity) {
		if (!in_array($this->type, class_implements($entity))) {
			throw new \Exception($entityType . " doesn't implement " . $this->type);
		}
		$this->driver->push($entity->marshal());
	}

	public function get() {
		$resp = $this->driver->get();
		if (is_null($resp)) {
			return [false, null];
		}

		$t = $this->type;
		return [$this->getId(), $t::unmarshal($resp)];
	}

	public function close() {
		$this->driver->close();
	}
}
