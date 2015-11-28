<?php

namespace Fliglio\Borg\Amqp;

use Fliglio\Borg\Chan\ChanDriver;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpChanDriver implements ChanDriver {

	private $conn;
	private $id;

	private $exchangeName;
	private $queueName;

	public function __construct(AMQPStreamConnection $conn, $id = null) {
		$this->conn = $conn;

		if (is_null($id)) {
			$id = uniqid();
		}
		$this->id = $id;

		$this->queueName = "borg.".$id;
		$this->exchangeName = "";
		$this->initChannel();
	}
	
	public function getId() {
		return $this->id;
	}

	// submit $data as msg body
	public function add($data) {
		$body = json_encode($data);
		$msg = new AMQPMessage($body, array(
			'content_type' => 'application/json', 
			'delivery_mode' => 2
		));
		$this->ch->basic_publish($msg, $this->exchangeName, $this->queueName);
	}

	// return array, null for none found
	public function get() {
		$msg = $this->ch->basic_get($this->queueName);
		if (is_null($msg)) {
			return null;
		}
		$this->ch->basic_ack($msg->delivery_info['delivery_tag']);
		
		$data =  json_decode($msg->body);
		return $data;
	}

	// close & delete connection/queue
	public function close() {
		$this->ch->close();
	}
	
	private function initChannel() {
		$ch = $this->conn->channel();
		
		/*
		    name: $queue
		    passive: false
		    durable: true // the queue will survive server restarts
		    exclusive: false // the queue can be accessed in other channels
		    auto_delete: false //the queue won't be deleted once the channel is closed.
		*/
		$ch->queue_declare($this->queueName, false, true, false, false);

		/*
		    name: $exchange
		    type: direct
		    passive: false
		    durable: true // the exchange will survive server restarts
		    auto_delete: false //the exchange won't be deleted once the channel is closed.
		*/
//		$ch->exchange_declare($this->exchangeName, 'direct', false, true, false);

//		$ch->queue_bind($this->queueName, $this->exchangeName);
		$this->ch = $ch;
	}
}
