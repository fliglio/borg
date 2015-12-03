<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Web\MappableApi;
use Fliglio\Borg\CollectiveDriver;
use Fliglio\Borg\Chan\ChanDriver;
use Fliglio\Borg\Type\Primitive;
class Chan {
	const CLASSNAME = __CLASS__;

	private $id;
	private $type;
	private $driver;

	public function __construct($type, CollectiveDriver $factory, $id = null) {
		if (!in_array('Fliglio\Web\MappableApi', class_implements($type))) {
			throw new \Exception(sprintf("Type '%s' doesn't implement MappableApi", $type));
		}
		$this->type = $type;
		
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
		if (!is_object($entity)) {
			$entity = new Primitive($entity);
		}
		if (!is_a($entity, $this->type)) {
			throw new \Exception("add entity doesn't implement " . $this->type);
		}
		$this->driver->add($entity->marshal());
	}

	public function get() {
		$resp = $this->driver->get(false);
		$t = $this->type;
		$e = $t::unmarshal($resp);
		if (is_a($e, Primitive::getClass())) {
			return $e->value();
		}
		return $e;
	}
	public function getnb() {
		$resp = $this->driver->get(true);
		if (is_null($resp)) {
			return [null, null];
		}

		$t = $this->type;
		return [$this->getId(), $t::unmarshal($resp)];
	}

	public function close() {
		$this->driver->close();
	}
}
