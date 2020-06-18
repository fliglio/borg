<?php

namespace Fliglio\Borg;

use Fliglio\Http\Http;
use Fliglio\Routing\Type\RouteBuilder;
use Fliglio\Fli\Configuration\DefaultConfiguration;

use GuzzleHttp\Client;

use Fliglio\Borg\Amqp\AmqpCollectiveDriver;
use Fliglio\Borg\Amqp\AmqpChanDriverFactory;
use Fliglio\Borg\Mapper\DefaultMapper;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class TestConfiguration extends DefaultConfiguration {

	public function getRoutes() {
		$rConn = new AMQPStreamConnection('localhost', 5672, "guest", "guest", "/");

		$resource = new TestResource();
		$fun = new FunResource;
		$shake = new ShakespeareResource;

		$coll = (new AmqpCollectiveFactory($rConn, "borg-demo"))
			->create()
			->assimilate($resource)
			->assimilate($fun)
			->assimilate($shake);



		return [
			RouteBuilder::get()
				->uri('/round-trip')
				->resource($resource, 'roundTrip')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/chan-chan')
				->resource($resource, 'chanChan')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/generate-numbers')
				->resource($resource, 'generateNumbers')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/generate-numbers-2')
				->resource($resource, 'generateNumbersTwo')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/sync')
				->resource($resource, 'syncEx')
				->method(Http::METHOD_GET)
				->build(),

			RouteBuilder::get()
				->uri('/fibonacci')
				->resource($fun, 'fibonacci')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/pi')
				->resource($fun, 'pi')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/prime')
				->resource($fun, 'prime')
				->method(Http::METHOD_GET)
				->build(),
		
			RouteBuilder::get()
				->uri('/shakespeare/words')
				->resource($shake, 'allWords')
				->method(Http::METHOD_GET)
				->build(),
			RouteBuilder::get()
				->uri('/shakespeare/words-sync')
				->resource($shake, 'allWordsSync')
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


