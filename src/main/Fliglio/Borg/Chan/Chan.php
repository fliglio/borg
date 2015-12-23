<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Borg\CollectiveDriver;

class Chan {
	const CLASSNAME = __CLASS__;

	private $driver;
	
	private $mapper;

	public function __construct($type, CollectiveDriver $factory, $id = null) {
		$this->mapper = new ChanTypeMapper($type, $factory);
		
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
		$vo = $this->mapper->marshal($entity);
		$this->driver->add($vo);
	}

	/**
	 * Get an entity from the chan; block until one is ready
	 */
	public function get() {
		$resp = $this->driver->get(false);
		return $this->mapper->unmarshal($resp);
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
		return [true, $this->mapper->unmarshal($resp)];
	}

	/**
	 * close connection to the Chan
	 */
	public function close() {
		$this->driver->close();
	}
}
