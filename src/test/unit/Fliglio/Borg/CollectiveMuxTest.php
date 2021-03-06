<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Api\Foo;
use Fliglio\Flfc\Request;
use Fliglio\Borg\Mapper\DefaultMapper;
use Fliglio\Borg\Test\MockCollectiveDriverFactory;

class CollectiveMux extends \PHPUnit_Framework_TestCase {
	use BorgImplant;

	private $driver;
	private $mapper;
	private $routing;
		

	const OPT_ARG_DEFAULT = "I'm The Default";

	public function setup() {
		$this->driver = MockCollectiveDriverFactory::get();
		$this->routing = new RoutingConfiguration("borg-demo");
		$this->mapper = new DefaultMapper($this->driver);
		
	}
	private function buildRoutineRequest(array $args, $method) {
		return (new RoutineRequestBuilder())
			->ns('foo')
			->dc('bar')
			->type(get_class($this))
			->method($method)
			->args($args)
			->retryErrors(false)
			->build();
	}
	private function buildRequest(array $entities) {
		$r = $this->buildRoutineRequest($entities, 'myTestMethod');

		return $this->mapper->marshalRoutineRequest($r);
	}

	public function myTestMethod($msg, Chan $ch, Foo $foo, $optArg = self::OPT_ARG_DEFAULT) {
		return [$msg, $ch, $foo, $optArg];
	}

	public function testMux() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
			"hello",
		];
		$req = $this->buildRequest($args);

		// when
		$resp = $coll->mux($req);

		// then
		$this->assertEquals($args, $resp, 'Unmarshalled vos should match original entities');
	}
	public function testMuxWithDefaultParameter() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];
		$req = $this->buildRequest($args);
		$expected = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
			self::OPT_ARG_DEFAULT,
		];

		// when
		$resp = $coll->mux($req);

		// then
		
		$this->assertEquals($expected, $resp, 'Unmarshalled vos should match original entities plus the default for the optional arg');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testMuxNoRoutingKey() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];
		
		$req2 = $this->buildRequest($args);
		
		$req = new Request();
		$req->setBody($req2->getBody());

		// when
		$resp = $coll->mux($req);
	}
	
}
