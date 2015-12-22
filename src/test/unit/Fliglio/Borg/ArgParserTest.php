<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;

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

	public function testMarshalChan() {
		// given
		$entities = [
			new Chan(null, $this->driver),
			new Chan(null, $this->driver, 'asdf1234'),
			new Chan(Chan::CLASSNAME, $this->driver)
			// add a mappableApi
		];
		
		$types = array_fill(0, count($entities), 'Fliglio\Borg\Chan\Chan');

		// when
		$vos = ArgParser::marshalArgs($entities);
		
		$found = ArgParser::unmarshalArgs($this->driver, $types, $vos);

		// then
		$this->assertEquals($entities, $found, 'Unmarshalled vos should match original entities');
	}
}
