<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;
use Fliglio\Borg\Api\Foo;

class ArgParserTest extends \PHPUnit_Framework_TestCase {
	private $driver;

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
		$vos = ArgParser::marshalArgs($entities);
		
		$found = ArgParser::unmarshalArgs($this->driver, $types, $vos);

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
		$vos = ArgParser::marshalArgs($entities);
		
		$found = ArgParser::unmarshalArgs($this->driver, $types, $vos);

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
	}
	
	public function testMarshalChan() {
		// given
		$entities = [
			new Chan(null, $this->driver),
			new Chan(null, $this->driver, uniqid()),
			new Chan(Chan::CLASSNAME, $this->driver),
			new Chan(Chan::CLASSNAME, $this->driver, uniqid()),
			new Chan(Foo::getClass(), $this->driver),
			new Chan(Foo::getClass(), $this->driver, uniqid()),
		];
		
		$types = array_fill(0, count($entities), Chan::CLASSNAME);

		// when
		$vos = ArgParser::marshalArgs($entities);
		
		$found = ArgParser::unmarshalArgs($this->driver, $types, $vos);

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
	}
	
	public function testMarshalMix() {
		// given
		$entities = [
			"asdf",
			new Foo("foo"),
			new Chan(Foo::getClass(), $this->driver),
			false,
			new Foo("bar"),
			new Chan(null, $this->driver),
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
		$vos = ArgParser::marshalArgs($entities);
		
		$found = ArgParser::unmarshalArgs($this->driver, $types, $vos);

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
	}
}
