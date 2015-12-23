<?php

namespace Fliglio\Borg\Amqp;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use Fliglio\Borg\Driver\CollectiveDriver;
use Fliglio\Borg\Chan;

class AmqpCollectiveDriver implements CollectiveDriver {

	const EXCHANGE = "borg";

	private $conn;
	private $ch;

	private $chans = [];

	public function __construct(AMQPStreamConnection $conn) {
		$this->conn = $conn;
	}
	
	/**
	 * Factory method to create an AmqpChanDriver
	 */
	public function createChan($id = null) {
		if (is_null($id) || !isset($this->chans[$id])) {
			$chanDriver = new AmqpChanDriver($this->conn, $id);
			$id = $chanDriver->getId();
			$this->chans[$id] = $chanDriver;
		}
		return $this->chans[$id];
	}

	public function createChanReader(array $chans) {
		return new AmqpChanReaderDriver($this, $chans);
	}


	/**
	 *  Publish args with a routing_key containing routing details to exchange
	 */
	public function go($routingKey, array $data) {
		
		$ch = $this->getChannel();
		
		$msg = new AMQPMessage(json_encode($data), array('content_type' => 'application/json'));


		$ch->basic_publish($msg, self::EXCHANGE, $routingKey);
	}

	/**
	 * close connection to rabbitmq channel
	 */
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
