<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;
use Fliglio\Borg\Api\Foo;

class ChanTest extends \PHPUnit_Framework_TestCase {
	private $driver;

	public static $q = [];

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
				$chanDriver->method('get')
					->will($this->returnCallback(function() {
						return array_shift(ChanTest::$q);
					}));
				$chanDriver->method('add')
					->will($this->returnCallback(function($vo) {
						array_push(ChanTest::$q, $vo);
					}));

				return $chanDriver;
			}));
	}


	public function testChanPrimitive() {
		// given
		$entity = "Hello World";
		$ch = new Chan(null, $this->driver);

		// when
		$ch->add($entity);
		$found = $ch->get();

		// then
		$this->assertEquals($entity, $found, 'chan get should return same entity that was added');
	}

	public function testChanMappableApi() {
		// given
		$entity = new Foo("Hello World");
		$ch = new Chan(Foo::getClass(), $this->driver);

		// when
		$ch->add($entity);
		$found = $ch->get();

		// then
		$this->assertEquals($entity, $found, 'chan get should return same entity that was added');
	}

	public function testChanChan() {
		// given
		$entity = new Chan(null, $this->driver);
		$ch = new Chan(Chan::CLASSNAME, $this->driver);

		// when
		$ch->add($entity);
		$found = $ch->get();

		// then
		$this->assertEquals($entity, $found, 'chan get should return same entity that was added');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testChanUnmappableType() {
		// given
		$entity = new self();
		$ch = new Chan(get_class($this), $this->driver);

		// when
		$ch->add($entity);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testChanWrongType() {
		// given
		$entity = new Foo("Hello World");
		$ch = new Chan(null, $this->driver);

		// when
		$ch->add($entity);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testChanWrongType2() {
		// given
		$entity = "foo";
		$ch = new Chan(Foo::getClass(), $this->driver);

		// when
		$ch->add($entity);
	}
}

