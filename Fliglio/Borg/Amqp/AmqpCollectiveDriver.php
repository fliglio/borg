<?php

namespace Fliglio\Borg\Amqp;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use Fliglio\Borg\CollectiveDriver;

class AmqpCollectiveDriver implements CollectiveDriver {

	const EXCHANGE = "borg";

	private $conn;

	public function __construct(AMQPStreamConnection $conn) {
		$this->conn = $conn;
	}
	
	public function go($routingKey, array $data) {
		
		
		$ch = $this->conn->channel();
		$ch->exchange_declare(self::EXCHANGE, 'topic', false, true, false);
		
		
		$msg = new AMQPMessage(json_encode($data), array('content_type' => 'application/json'));


		$ch->basic_publish($msg, self::EXCHANGE, $routingKey);
		$ch->close();
	}
	
	// submit $data as msg body
	public function push($id, array $data) {
	
	}

	// return array, null for none found
	public function get($id) {
	
	}

	// close & delete connection/queue
	public function closeChan($id) {
	
	}
	// close & delete connection/queue
	public function close() {
	
	}
}
