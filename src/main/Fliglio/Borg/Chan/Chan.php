<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Borg\CollectiveDriver;
use Fliglio\Borg\Type\ArgMapper;

class Chan {
	const CLASSNAME = __CLASS__;

	private $factory;
	private $driver;
	
	public function __construct($type, CollectiveDriver $factory, $id = null) {
		$this->factory = $factory;
		$this->driver = $factory->createChan($id);
		$this->type = $type;
	}
	/**
	 * Get the Chan's type, all entities added to this chan must conform
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Unique Identifier for this chan across all services / processes
	 */
	public function getId() {
		return $this->driver->getId();
	}

	/**
	 * Add an entity to the Chan
	 */
	public function add($entity) {
		$vo = ArgMapper::marshalArg($entity, $this->type);
		$this->driver->add($vo);
	}

	/**
	 * Get an entity from the chan; block until one is ready
	 */
	public function get() {
		$resp = $this->driver->get(false);
		return ArgMapper::unmarshalArg($this->factory, $resp, $this->type);
	}

	/**
	 * Get an entity from the chan; if none available, return immediately.
	 * Also return whether or not an entity was found with the entity
	 * to allow for chans to return an actual "null" entity
	 */
	public function getnb() {
		$resp = $this->driver->get(true);
		if (is_null($resp)) {
			return [false, null];
		}
		return [true, ArgMapper::unmarshalArg($this->factory, $resp, $this->type)];
	}

	/**
	 * close connection to the Chan
	 */
	public function close() {
		$this->driver->close();
	}
}
