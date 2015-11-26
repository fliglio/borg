<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Web\MappableApi;
use Fliglio\Borg\MessagingDriver;
use Fliglio\Borg\Chan\ChanDriver;

class Chan {

	private $id;
	private $type;
	private $driver;

	public function __construct($type, MessagingDriver $driver) {
		$this->id = uniqid();
		$this->type = $type;
		$this->driver = new ChanDriver($driver, $this->id);

	}

	public function getId() {
		return $this->id;
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
