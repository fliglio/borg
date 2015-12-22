<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;
use Fliglio\Borg\Api\Foo;

class InvokerTest extends \PHPUnit_Framework_TestCase {
	private $driver;

	public $msg;
	public $ch;
	public $foo;

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

		$this->msg = "hello world";
		$this->ch = new Chan(null, $this->driver);
		$this->foo = new Foo("bar");
	}

	public function myTestMethod($msg, Chan $ch, Foo $foo) {
		return [$msg, $ch, $foo];
	}

	public function testInvokeMethod() {
		// given
		$invoker = new CollectiveInvoker($this->driver);
		
		$args = [$this->msg, $this->ch, $this->foo];

		$vos = ArgParser::marshalArgs($args);

		// when
		$resp = $invoker->dispatchRequest($this, 'myTestMethod', $vos);


		// then
		$this->assertEquals($args, $resp, 'Unmarshalled vos should match original entities');
	}

}