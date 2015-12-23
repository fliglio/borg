<?php

namespace Fliglio\Borg;

class ChanReader {

	private $chans;

	/**
	 * Create a chan reader for a collection of Chans
	 *
	 * Array order matters here: there is no "load balancing" across chans
	 * and an entity from a chan early in the array will be returned until
	 * it has none available.
	 */
	public function __construct(array $chans) {
		$this->chans = $chans;
	}


	/**
	 * Get the next entity ready off a collection of Chans.
	 *
	 * The Chan's `id` will be returned with the entity to help client code
	 * correlate the entity with which chan it came from.
	 */
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
