<?php

namespace Fliglio\Borg\Amqp;

use Fliglio\Borg\Driver\ChanReaderDriver;
use Fliglio\Borg\Chan;

class AmqpChanReaderDriver implements ChanReaderDriver {

	private $drivers = [];

	public function __construct(AmqpCollectiveDriver $factory, array $chans) {
		foreach ($chans as $chan) {
			if (!$chan instanceof Chan) {
				throw new \Exception("Elements in Chan array must be of type Chan");
			}
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