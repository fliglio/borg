<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Driver\CollectiveDriver;
use Fliglio\Borg\Driver\WireMapper;


class Chan {
	const CLASSNAME = __CLASS__;

	private $factory;
	private $driver;

	private $mapper;
	
	public function __construct($type, CollectiveDriver $factory, WireMapper $mapper, $id = null) {
		$this->factory = $factory;
		$this->driver = $factory->createChan($id);
		$this->type = $type;

		$this->mapper = $mapper;
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
		$vo = $this->mapper->marshalArg($entity, $this->type);
		$this->driver->add($vo);
	}

	/**
	 * Get an entity from the chan; block until one is ready
	 */
	public function get() {
		$resp = $this->driver->get();
		return $this->mapper->unmarshalArg($resp, $this->type);
	}

	/**
	 * close connection to the Chan
	 */
	public function close() {
		$this->driver->close();
	}
}
