<?php


namespace Fliglio\Borg;


use Fliglio\Borg\Amqp\AmqpCollectiveDriver;
use Fliglio\Borg\Mapper\DefaultMapper;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class AmqpCollectiveFactory {

	private $rabbitConnection;
	private $routingNamespace;

	public function __construct(AMQPStreamConnection $rabbitConnection, $routingNamespace) {
		$this->rabbitConnection = $rabbitConnection;
		$this->routingNamespace = $routingNamespace;
	}

	public function create() {
		$driver  = new AmqpCollectiveDriver($this->rabbitConnection);
		$mapper  = new DefaultMapper($driver);
		$routing = new RoutingConfiguration($this->routingNamespace);

		return new Collective($driver, $mapper, $routing);
	}
}

