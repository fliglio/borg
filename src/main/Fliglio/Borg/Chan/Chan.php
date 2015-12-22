<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Borg\CollectiveDriver;

class Chan {
	const CLASSNAME = __CLASS__;

	private $id;
	private $type; // chan type or null for primitive
	private $factory;
	private $driver;
	
	private $mapper;

	public function __construct($type, CollectiveDriver $factory, $id = null) {
		$this->mapper = new ChanTypeMapper($type, $factory);
		
		$this->type = $type;
		$this->factory = $factory;

		if (is_null($id)) {
			$this->driver = $factory->createChan();
		} else {
			$this->driver = $factory->createChan($id);
		}
	}
	public function getType() {
		return $this->type;
	}

	public function getId() {
		return $this->driver->getId();
	}

	public function add($entity) {
		$vo = $this->mapper->marshal($entity);
		$this->driver->add($vo);
	}

	public function get() {
		$resp = $this->driver->get(false);
		return $this->mapper->unmarshal($resp);
	}

	public function getnb() {
		$resp = $this->driver->get(true);
		if (is_null($resp)) {
			return [null, null];
		}
		return [
			$this->getId(),
			$this->mapper->unmarshal($resp),
		];
	}

	public function close() {
		$this->driver->close();
	}
}
