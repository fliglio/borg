<?php
namespace Fliglio\Borg\Mapper;

use Fliglio\Borg\Chan;
use Fliglio\Borg\Api\Foo;
use Fliglio\Borg\Test\MockCollectiveDriverFactory;
use Fliglio\Borg\RoutineRequestBuilder;

class DefaultMapperRequestTest extends \PHPUnit_Framework_TestCase {
	private $driver;
	private $mapper;

	public function setup() {
		$this->driver = MockCollectiveDriverFactory::get();
		$this->mapper = new DefaultMapper($this->driver);
	}
	private function buildRequest(array $args, $method) {
		return (new RoutineRequestBuilder())
			->ns('foo')
			->dc('bar')
			->type(get_class($this))
			->method($method)
			->args($args)
			->retryErrors(false)
			->build();
	}
	
	public function StubCollectiveRoutineMethod($prim, Chan $ch, Foo $foo, $optionalArg = null) {}
		
		public function testMarshalRequest() {
		// given
		$entities = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
			123,
		];
		
		$req = $this->buildRequest($entities, 'StubCollectiveRoutineMethod');

		// when
		$r = $this->mapper->marshalRoutineRequest($req);
		$found = $this->mapper->unmarshalRoutineRequest($r);

		// then
		$this->assertEquals($req, $found, 'Unmarshalled request should match initial request');
	}
	public function testMarshalRequestWithDefaults() {
		// given
		$entities = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];
		$req = $this->buildRequest($entities, 'StubCollectiveRoutineMethod');

		// when
		$r = $this->mapper->marshalRoutineRequest($req);
		$found = $this->mapper->unmarshalRoutineRequest($r);

		// then
		$this->assertEquals($req, $found, 'Unmarshalled request should match initial request');
	}
	
	/**
	 * @expectedException \Exception
	 */
	public function testMarshalWrongTypes() {
		// given
		$entities = [
			"hello world",
			"hello world",
			"hello world",
			"hello world",
		];

		$req = $this->buildRequest($entities, 'StubCollectiveRoutineMethod');

		// when
		$r = $this->mapper->marshalRoutineRequest($req);
	}

}