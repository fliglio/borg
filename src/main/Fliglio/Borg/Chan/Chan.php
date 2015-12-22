<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Web\MappableApi;
use Fliglio\Borg\CollectiveDriver;
use Fliglio\Borg\Chan\ChanDriver;
use Fliglio\Borg\Type\Primitive;
use Fliglio\Borg\ArgParser;

class Chan {
	const CLASSNAME = __CLASS__;

	private $id;
	private $type; // chan type, or null for primitive
	private $factory;
	private $driver;

	public function __construct($type, CollectiveDriver $factory, $id = null) {
		if (!is_null($type)) {
			if (!in_array('Fliglio\Web\MappableApi', class_implements($type))) {
				if ('Fliglio\Borg\Chan\Chan' != $type) {
					throw new \Exception(sprintf("Type '%s' isn't marshallable", $type));
				}
			}
		}
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
		if (is_null($this->type)) {
			if (is_object($entity)) {
				throw new \Exception("This Chan::add() expects a primitive");
			}
		} else {
			if (!is_object($entity) || !is_a($entity, $this->type)) {
				throw new \Exception(sprintf("This Chan::add() expects a %s", $this->type));
			}
		}
		$this->driver->add(ArgParser::marshalArg($entity));
	}

	public function get() {
		$resp = $this->driver->get(false);

		return ArgParser::unmarshalArg($this->factory, $this->type, $resp);
	}

	public function getnb() {
		$resp = $this->driver->get(true);
		if (is_null($resp)) {
			return [null, null];
		}
		return [
			$this->getId(),
			ArgParser::unmarshalArg($this->factory, $this->type, $resp)
		];
	}

	public function close() {
		$this->driver->close();
	}
}
