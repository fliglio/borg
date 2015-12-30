<?php
namespace Fliglio\Borg\Mapper;

use Fliglio\Borg\Chan;
use Fliglio\Borg\Api\Foo;
use Fliglio\Borg\Test\MockCollectiveDriverFactory;
use Fliglio\Borg\RoutineRequest;
use Fliglio\Borg\TopicConfiguration;

class DefaultMapperRequestTest extends \PHPUnit_Framework_TestCase {
	private $driver;
	private $mapper;
	private $ex;

	public function setup() {
		$this->driver = MockCollectiveDriverFactory::get();
		$this->mapper = new DefaultMapper($this->driver);
		$this->ex = new Chan(null, $this->driver, $this->mapper);
	}

	public function StubCollectiveRoutineMethod($prim, Chan $ch, Foo $foo, $optionalArg = null) {}
	public function StubCollectiveRoutineMethod2(Chan $ch, Foo $foo, $prim) {}
	public function testMarshalRequest() {
		// given
		$topic = new TopicConfiguration('foo', 'bar', $this, 'StubCollectiveRoutineMethod');
		$entities = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
			123,
		];
		
		$req = new RoutineRequest($topic, $entities, $this->ex, false);

		// when
		$r = $this->mapper->marshalRoutineRequest($req);
		$found = $this->mapper->unmarshalRoutineRequest($r);

		// then
		$this->assertEquals($req, $found, 'Unmarshalled request should match initial request');
	}
	public function testMarshalRequestWithDefaults() {
		// given
		$topic = new TopicConfiguration('foo', 'bar', $this, 'StubCollectiveRoutineMethod');
		$entities = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];
		$req = new RoutineRequest($topic, $entities, $this->ex, false);

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
		$topic = new TopicConfiguration('foo', 'bar', $this, 'StubCollectiveRoutineMethod');
		$entities = [
			"hello world",
			"hello world",
			"hello world",
			"hello world",
		];

		$req = new RoutineRequest($topic, $entities, $this->ex, false);

		// when
		$r = $this->mapper->marshalRoutineRequest($req);
	}

}

