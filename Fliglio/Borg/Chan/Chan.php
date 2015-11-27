<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Web\MappableApi;
use Fliglio\Borg\MessagingDriver;
use Fliglio\Borg\Chan\ChanDriver;

class Chan {
	const CLASSNAME = __CLASS__;

	private $id;
	private $type;
	private $driver;

	public function __construct($type, CollectionDriver $factory, $id = null) {
		$this->type = $type;
		
		if (is_null($id)) {
			$this->driver = $factory->createChan();
		} else {
			$this->driver = $factory->createChan($id);
		}
	}

	public function getId() {
		return $this->driver->getId();
	}

	public function add(MappableApi $entity) {
		if (!in_array($this->type, class_implements($entity))) {
			throw new \Exception($entityType . " doesn't implement " . $this->type);
		}
		$this->driver->add($entity->marshal());
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
