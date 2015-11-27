<?php

namespace Fliglio\Borg\Amqp;

use Fliglio\Borg\Chan\ChanDriver;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class AmqpChanDriver implements ChanDriver {

	private $conn;
	private $id;

	public function __construct(AMQPStreamConnection $conn, $id = null) {
		$this->conn = $conn;

		if (is_null($id)) {
			$id = uniqid();
		}
		$this->id = $id;
	}
	
	public function getId() {
		return $this->id;
	}

	// submit $data as msg body
	public function add(array $data) {
	
	}

	// return array, null for none found
	public function get() {
	
	}

	// close & delete connection/queue
	public function close() {
	
	}
}
