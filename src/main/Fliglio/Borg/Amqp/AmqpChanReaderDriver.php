<?php

namespace Fliglio\Borg\Amqp;

use Fliglio\Borg\Driver\ChanReaderDriver;

class AmqpChanReaderDriver implements ChanReaderDriver {

	private $drivers = [];

	public function __construct(AmqpCollectiveDriver $factory, array $chans) {

		foreach ($chans as $chan) {
			$this->drivers[] = $factory->createChan($chan->getId());
		}
	}

	/**
	 * Get the next entity ready off a collection of Chans.
	 *
	 * The Chan's `id` will be returned with the entity to help client code
	 * correlate the entity with which chan it came from.
	 */
	public function get() {
		while (true) {
			foreach ($this->drivers as $driver) {
				$resp = $driver->nonBlockingGet();

				if (!is_null($resp)) {
					return [$driver->getId(), $resp];
				}
			}
			usleep(1000); // 1 millisecond
		}
	}
}
