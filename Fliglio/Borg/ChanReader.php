<?php

namespace Fliglio\Borg;

class ChanReader {
	
	private $driver;

	public function __construct(MessageDriver $driver) {
		$this->driver = $driver;
	}

	public function handle(Chan $chan, $handler) {

		return $this;
	}

	public function next() {
	
	}
	
}
