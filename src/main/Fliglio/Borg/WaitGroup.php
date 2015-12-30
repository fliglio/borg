<?php

namespace FLiglio\Borg;

class WaitGroup {
	
	private $reader;
	private $numRoutines;

	public function __construct(ChanReader $reader, $numRoutines) {
		$this->reader = $reader;
		$this->numRoutines = $numRoutines;
	}

	public function wait() {
		for ($i = 0; $i < $this->numRoutines; $i++) {
			list($n, $e) = $this->reader->get();
			if (!is_null($e)) {
				throw new \Exception($e);
			}
		}
	
	}
}
