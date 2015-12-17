<?php

namespace Fliglio\Borg\Chan;

class ChanReader {

	private $chans;

	public function __construct(array $chans) {
		$this->chans = $chans;
	}


	public function get() {
		while (true) {
			foreach ($this->chans as $chan) {
				list($found, $entity) = $chan->getnb();
				if ($found) {
					return [$chan->getId(), $entity];
				}
			}
			usleep(1000); // 1 millisecond
		}
	}

}
