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
	
	private function getChannel() {
		if (!isset($this->ch)) {
			$queueName = "borg-".$this->getId();
			$ch = $this->conn->channel();

			/*
			    name: $queue
			    passive: false
			    durable: true // the queue will survive server restarts
			    exclusive: false // the queue can be accessed in other channels
			    auto_delete: false //the queue won't be deleted once the channel is closed.
			*/
			$ch->queue_declare($queue, false, true, false, false);
		
			$ch->queue_bind($queue, "");
		}
		return $this->ch;
	}
}
