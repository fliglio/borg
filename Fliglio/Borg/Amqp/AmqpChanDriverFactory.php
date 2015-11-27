<?php

namespace Fliglio\Borg\Amqp;

use Fliglio\Borg\Chan\ChanDriverFactory;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class AmqpChanDriverFactory implements ChanDriverFactory {

	private $conn;

	public function __construct(AMQPStreamConnection $conn) {
		$this->conn = $conn;
	}

	public function create($id = null) {
		return new AmqpChanDriver($conn, $id);
	}

}
