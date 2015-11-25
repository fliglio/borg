<?php

namespace Fliglio\Borg\Chan;

class ChanReader {
	
	private $chans;

	public function __construct(array $chans) {
		$this->chans = $chans;
	}


	public function next() {
		foreach ($chans as $chan) {
			list($found, $entity) = $chan->get();
			if ($found) {
				return [$chan->getId(), $entity];
			}
		}
		return [false, null];
	}
	
}
