<?php

namespace Fliglio\Borg;

use Fliglio\Http\Http;
use Fliglio\Routing\Type\RouteBuilder;
use Fliglio\Fli\Configuration\DefaultConfiguration;

use GuzzleHttp\Client;

use Fliglio\Borg\Amqp\AmqpCollectiveDriver;
use Fliglio\Borg\Amqp\AmqpChanDriverFactory;
use Fliglio\Borg\Collective;
use Fliglio\Borg\Chan\ChanFactory;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class TestConfiguration extends DefaultConfiguration {


	protected function getTestResource() {
		return new TestResource();
	}

	public function getRoutes() {
		$rConn = new AMQPStreamConnection('localhost', 5672, "guest", "guest", "/");
		$driver = new AmqpCollectiveDriver($rConn);
	
		$resource = $this->getTestResource();
		

		$coll = new Collective($driver, "borg-demo", 'default');
		$coll->assimilate($resource);



		return [
			RouteBuilder::get()
				->uri('/test')
				->resource($resource, 'test')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/chan-chan')
				->resource($resource, 'chanChan')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/fibonacci')
				->resource($resource, 'fibonacci')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/pi')
				->resource($resource, 'pi')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/prime')
				->resource($resource, 'prime')
				->method(Http::METHOD_GET)
				->build(),
		
			// Router for all Borg Collective calls
			RouteBuilder::get()
				->uri('/borg')
				->resource($coll, "mux")
				->method(Http::METHOD_POST)
				->build(),

		];
	}

}


