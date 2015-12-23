<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Driver\CollectiveDriver;
use Fliglio\Borg\Driver\WireMapper;

class ChanReader {

	private $driver;
	private $mapper;
	private $chans;

	/**
	 * Create a chan reader for a collection of Chans
	 *
	 * Array order matters here: there is no "load balancing" across chans
	 * and an entity from a chan early in the array will be returned until
	 * it has none available.
	 */
	public function __construct(CollectiveDriver $factory, WireMapper $mapper, array $chans) {
		$this->driver = $factory->createChanReader($chans);
		$this->mapper = $mapper;
		$this->chans = $chans;
	}

	/**
	 * Get the next entity ready off a collection of Chans.
	 *
	 * The Chan's `id` will be returned with the entity to help client code
	 * correlate the entity with which chan it came from.
	 */
	public function get() {
		list($id, $resp) = $this->driver->get();
		return [$id, $this->unmarshal($id, $resp)];
	}
	
	private function unmarshal($id, $vo) {
		foreach ($this->chans as $chan) {
			if ($id == $chan->getId()) {
				return $this->mapper->unmarshalArg($vo, $chan->getType());
			}
		}
		throw new \Exception(sprintf("Chan id '%s' couldn't be found", $id));
	}
}
