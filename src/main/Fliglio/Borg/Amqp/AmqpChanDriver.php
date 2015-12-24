<?php

namespace Fliglio\Borg\Amqp;

use Fliglio\Borg\Driver\ChanDriver;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class AmqpChanDriver implements ChanDriver {

	private $conn;
	private $id;

	private $exchangeName;
	private $queueName;

	private $msgCache;

	public function __construct(AMQPStreamConnection $conn, $id = null) {
		$this->conn = $conn;

		$this->id = $id;

		$this->queueName = $id;
		$this->exchangeName = "borgchan";
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
		
		//msg, exchange, routing_key, mandatory, immediate
		$this->ch->basic_publish($msg, $this->exchangeName, $this->queueName);
	}

	// return array, null for none found
	public function get() {
		while (true) {
			$msg = $this->ch->basic_get($this->queueName);
			if (!is_null($msg)) {
				$this->ch->basic_ack($msg->delivery_info['delivery_tag']);
		
				$data =  json_decode($msg->body, true);
				return $data;
			}
			usleep(1000);
		}
	}

	/**
	 * Get the next message or null of none available
	 *
	 * (not part of ChanDriver api, support method for AmqpChanReaderDriver)
	 */
	public function nonBlockingGet() {
		$msg = $this->ch->basic_get($this->queueName);
		if (is_null($msg)) {
			return null;
		}
		$this->ch->basic_ack($msg->delivery_info['delivery_tag']);
		
		$data =  json_decode($msg->body);
		return $data;
	}

	// close connection
	public function close() {
		$this->ch->close();
	}
	
	private function initChannel() {
		$ch = $this->conn->channel();
		
		// name: $queue
		// passive: false
		// durable: true // the queue will survive server restarts
		// exclusive: false // the queue can be accessed in other channels
		// auto_delete: false //the queue won't be deleted once the channel is closed.
		// nowait: false
		// args: null
		if (is_null($this->queueName)) {
			list($queueName, , ) = $ch->queue_declare(
				"", false, false, false, 
				false, false, new AMQPTable(['x-expires' => 10000])
			);

			$this->queueName = $queueName;
			$this->id = $queueName;
		}
		$ch->basic_qos(null, 1, null);
		
		// name: $exchange
		// type: direct
		// passive: false
		// durable: true // the exchange will survive server restarts
		// auto_delete: false //the exchange won't be deleted once the channel is closed.
		$ch->exchange_declare($this->exchangeName, 'direct', false, false, false);

		// queue
		// exchange
		// routing_key
		// nowait
		// args
		$ch->queue_bind($this->queueName, $this->exchangeName, $this->queueName);
		$this->ch = $ch;
	}
}
