<?php
namespace Fliglio\Borg\Mapper;

use Fliglio\Borg\Chan;
use Fliglio\Borg\Api\Foo;
use Fliglio\Borg\Test\MockCollectiveDriverFactory;

class DefaultMapperForMethodTest extends \PHPUnit_Framework_TestCase {
	private $driver;
	private $mapper;

	public function setup() {
		$this->driver = MockCollectiveDriverFactory::get();
		$this->mapper = new DefaultMapper($this->driver);
	}

	public function StubCollectiveRoutineMethod($prim, Chan $ch, Foo $foo, $optionalArg = null) {}
	public function StubCollectiveRoutineMethod2(Chan $ch, Foo $foo, $prim) {}
	public function testMarshalForMethod() {
		// given
		$entities = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
			123,
		];

		// when
		$vos = $this->mapper->marshalForMethod($entities, $this, 'StubCollectiveRoutineMethod');
		$found = $this->mapper->unmarshalForMethod($vos, $this, 'StubCollectiveRoutineMethod');

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
	}
	public function testMarshalForMethodWithDefaults() {
		// given
		$entities = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];

		// when
		$vos = $this->mapper->marshalForMethod($entities, $this, 'StubCollectiveRoutineMethod');
		$found = $this->mapper->unmarshalForMethod($vos, $this, 'StubCollectiveRoutineMethod');

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
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

		// when
		$vos = $this->mapper->marshalForMethod($entities, $this, 'StubCollectiveRoutineMethod');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testUnmarshalWrongTypes() {
		// given
		$entities = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];

		// when
		$vos = $this->mapper->marshalForMethod($entities, $this, 'StubCollectiveRoutineMethod');
		$found = $this->mapper->unmarshalForMethod($vos, $this, 'StubCollectiveRoutineMethod2');
	}
}

