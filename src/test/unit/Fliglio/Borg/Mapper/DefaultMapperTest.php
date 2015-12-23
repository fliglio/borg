<?php
namespace Fliglio\Borg\Mapper;

use Fliglio\Borg\Chan;
use Fliglio\Borg\Api\Foo;

class DefaultMapperTest extends \PHPUnit_Framework_TestCase {
	private $driver;
	private $mapper;

	public function setup() {
		$this->driver = $this->getMockBuilder('\Fliglio\Borg\Amqp\AmqpCollectiveDriver')
			->disableOriginalConstructor()
			->getMock();
		$this->driver->method('createChan')
			->will($this->returnCallback(function($id = null) {
				if (is_null($id)) {
					$id = uniqid();
				}
				$chanDriver = $this->getMockBuilder('\Fliglio\Borg\Amqp\AmqpChanDriver')
					->disableOriginalConstructor()
					->getMock();
		
				$chanDriver->method('getId')
					->willReturn($id);

				return $chanDriver;
			}));
		$this->mapper = new DefaultMapper($this->driver);
	}
	private function marshalArgs(array $args, array $types) {
		$out = [];
		for ($i = 0; $i < count($args); $i++) {
			$out[] = $this->mapper->marshalArg($args[$i], $types[$i]);
		}
		return $out;
	}
	private function unmarshalArgs(array $args, array $types) {
		$out = [];
		for ($i = 0; $i < count($args); $i++) {
			$out[] = $this->mapper->unmarshalArg($args[$i], $types[$i]);
		}
		return $out;
	}

	public function testMarshalPrimitive() {
		// given
		$entities = [
			"foo",
			123,
			1.2,
			false,
		];
		
		$types = array_fill(0, count($entities), null); // no hints are provided for primitives

		// when
		$vos = $this->marshalArgs($entities, $types);
		
		$found = $this->unmarshalArgs($vos, $types);

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
	}

	public function testMarshalMappableApi() {
		// given
		$entities = [
			new Foo("foo"),
			new Foo("bar"),
		];
		
		$types = array_fill(0, count($entities), Foo::getClass());

		// when
		$vos = $this->marshalArgs($entities, $types);
		
		$found = $this->unmarshalArgs($vos, $types);

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
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
		];
		
		$types = array_fill(0, count($entities), Chan::CLASSNAME);

		// when
		$vos = $this->marshalArgs($entities, $types);
		
		$found = $this->unmarshalArgs($vos, $types);

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
	}
	
	public function testMarshalMix() {
		// given
		$entities = [
			"asdf",
			new Foo("foo"),
			new Chan(Foo::getClass(), $this->driver, $this->mapper),
			false,
			new Foo("bar"),
			new Chan(null, $this->driver, $this->mapper),
		];
		
		$types = [
			null,
			Foo::getClass(),
			Chan::CLASSNAME,
			null,
			Foo::getClass(),
			Chan::CLASSNAME,
		];

		// when
		$vos = $this->marshalArgs($entities, $types);
		
		$found = $this->unmarshalArgs($vos, $types);

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
	}
}
