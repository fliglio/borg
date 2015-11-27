<?php

namespace Fliglio\Borg\Amqp;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use Fliglio\Borg\CollectiveDriver;

class AmqpCollectiveDriver implements CollectiveDriver {

	const EXCHANGE = "borg";

	private $conn;
	private $ch;

	public function __construct(AMQPStreamConnection $conn) {
		$this->conn = $conn;
	}

	// send message
	public function go($routingKey, array $data) {
		
		
		$ch = $this->getChannel();
		
		$msg = new AMQPMessage(json_encode($data), array('content_type' => 'application/json'));


		$ch->basic_publish($msg, self::EXCHANGE, $routingKey);
	}

	// close & delete connection/queue
	public function close() {
		$this->ch->close();
	}

	private function getChannel() {
		if (!isset($this->ch)) {
			$this->ch = $this->conn->channel();
			$this->ch->exchange_declare(self::EXCHANGE, 'topic', false, true, false);
		}
		return $this->ch;
	}
}
