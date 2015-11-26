<?php

namespace Fliglio\Borg;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class RabbitDriver implements MessagingDriver {

	const EXCHANGE = "borg";

	private $conn;

	public function __construct(AMQPStreamConnection $conn) {
		$this->conn = $conn;
	}
	
	public function go($type, $method, array $data) {
		
		
		$ch = $this->conn->channel();
		$ch->exchange_declare(self::EXCHANGE, 'topic', false, true, false);
		
		
		$msg = new AMQPMessage(json_encode($data), array('content_type' => 'application/json'));

		$routingKey = $type . "." . $method;

		$ch->basic_publish($msg, self::EXCHANGE, $routingKey);
		$ch->close();

	}
}
