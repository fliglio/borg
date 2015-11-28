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

	public function add(MappableApi $entity) {
		if (!is_a($entity, $this->type)) {
			throw new \Exception("add entity doesn't implement " . $this->type);
		}
		$this->driver->add($entity->marshal());
	}

	public function get($noBlock=false) {
		$resp = $this->driver->get($noBlock);
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
