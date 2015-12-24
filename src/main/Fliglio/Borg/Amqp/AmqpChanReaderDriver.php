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
	 * Find and return the next message from a collection of queues
	 */
	public function get() {
		while (true) {
			foreach ($this->drivers as $driver) {
				list($found, $resp) = $driver->nonBlockingGet();

				if ($found) {
					return [$driver->getId(), $resp];
				}
			}
			usleep(1000); // 1 millisecond
		}
	}
}
