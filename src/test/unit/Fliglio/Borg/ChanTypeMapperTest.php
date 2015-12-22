<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;
use Fliglio\Borg\Api\Foo;
use Fliglio\Borg\Chan\ChanTypeMapper;

class ChanTypeMapperTest extends \PHPUnit_Framework_TestCase {
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

	public function testMapPrimitive() {
		// given
		$entity = "hello world";
		$type = null;

		// when
		$m = new ChanTypeMapper($type, $this->driver);

		$vo = $m->marshal($entity);
		$found = $m->unmarshal($vo);

		// then
		$this->assertEquals($entity, $found, "unmarshalled should match original entity");
	}
	
	public function testMapMappableApi() {
		// given
		$entity = new Foo("hello world");
		$type = Foo::getClass();

		// when
		$m = new ChanTypeMapper($type, $this->driver);

		$vo = $m->marshal($entity);
		$found = $m->unmarshal($vo);

		// then
		$this->assertEquals($entity, $found, "unmarshalled should match original entity");
	}
	
	public function testMapChan() {
		// given
		$entity = new Chan(null, $this->driver);
		$type = Chan::CLASSNAME;

		// when
		$m = new ChanTypeMapper($type, $this->driver);

		$vo = $m->marshal($entity);
		$found = $m->unmarshal($vo);

		// then
		$this->assertEquals($entity, $found, "unmarshalled should match original entity");
	}

	/**
	 * @expectedException \Exception
	 */
	public function testUnmappableType() {
		// given
		$type = get_class($this);

		// when
		new ChanTypeMapper($type, $this->driver);
	}
	
	/**
	 * @expectedException \Exception
	 */
	public function testInvalidType() {
		// given
		$type = 123;

		// when
		new ChanTypeMapper($type, $this->driver);
	}
	/**
	 * @expectedException \Exception
	 */
	public function testTypeMismatch() {
		// given
		$entity = "hello world";
		$type = Foo::getClass();

		// when
		$m = new ChanTypeMapper($type, $this->driver);

		$m->marshal($entity);
	}
	/**
	 * @expectedException \Exception
	 */
	public function testTypeMismatch2() {
		// given
		$entity = new Foo("hello world");
		$type = Chan::CLASSNAME;

		// when
		$m = new ChanTypeMapper($type, $this->driver);

		$m->marshal($entity);
	}
}
