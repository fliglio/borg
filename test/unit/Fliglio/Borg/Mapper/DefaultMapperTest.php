<?php
namespace Fliglio\Borg\Mapper;

use Fliglio\Borg\Chan;
use Fliglio\Borg\Api\Foo;
use Fliglio\Borg\Test\MockCollectiveDriverFactory;

class DefaultMapperTest extends \PHPUnit_Framework_TestCase {
	private $driver;
	private $mapper;

	public function setup() {
		$this->driver = MockCollectiveDriverFactory::get();
		$this->mapper = new DefaultMapper($this->driver);
	}

	public function testMarshalPrimitive() {
		// given
		$entities = [
			"foo",
			123,
			1.2,
			false,
			["foo", "bar"],
			null,
		];
		
		foreach ($entities as $entity) {
			
			// when
			$vo = $this->mapper->marshalArg($entity, null); // null type for primitive
			$found = $this->mapper->unmarshalArg($vo, null);

			// then
			$this->assertEquals($entity, $found, 'Unmarshalled vos should match original entities');
		}
	}

	public function testMarshalMappableApi() {
		// given
		$entities = [
			new Foo("foo"),
			null,
		];
		
		foreach ($entities as $entity) {
			
			// when
			$vo = $this->mapper->marshalArg($entity, Foo::getClass());
			$found = $this->mapper->unmarshalArg($vo, Foo::getClass());

			// then
			$this->assertEquals($entity, $found, 'Unmarshalled vos should match original entities');
		}
	}
	
	public function testMarshalChan() {
		// given
		$entities = [
			new Chan(null, $this->driver, $this->mapper),
			new Chan(null, $this->driver, $this->mapper, uniqid()),
			new Chan(Chan::CLASSNAME, $this->driver, $this->mapper),
			new Chan(Chan::CLASSNAME, $this->driver, $this->mapper, uniqid()),
			new Chan(Foo::getClass(), $this->driver, $this->mapper),
			new Chan(Foo::getClass(), $this->driver, $this->mapper, uniqid()),
			null,
		];
		
		foreach ($entities as $entity) {
			
			// when
			$vo = $this->mapper->marshalArg($entity, Chan::CLASSNAME);
			$found = $this->mapper->unmarshalArg($vo, Chan::CLASSNAME);

			// then
			$this->assertEquals($entity, $found, 'Unmarshalled vos should match original entities');
		}
	}
	
}