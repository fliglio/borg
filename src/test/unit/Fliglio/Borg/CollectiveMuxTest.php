<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Chan\Chan;
use Fliglio\Borg\Api\Foo;
use Fliglio\Flfc\Request;

class CollectiveMux extends \PHPUnit_Framework_TestCase {
	use BorgImplant;

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

	public function testMux() {
		// given
		$coll = new Collective($this->driver, "test", "default");
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo];
		$vos = ArgParser::marshalArgs($args);
		
		$topic = new TopicConfiguration("test", "default", get_class($this), "myTestMethod");
		
		$req = new Request();
		$req->addHeader("X-routing-key", $topic->getTopicString());
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);

		// then
		$this->assertEquals($args, $resp, 'Unmarshalled vos should match original entities');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testMuxNoRoutingKey() {
		// given
		$coll = new Collective($this->driver, "test", "default");
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo];
		$vos = ArgParser::marshalArgs($args);
		
		$topic = new TopicConfiguration("test", "default", get_class($this), "myTestMethod");
		
		$req = new Request();
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);

		// then
		$this->assertEquals($args, $resp, 'Unmarshalled vos should match original entities');
	}
	
	/**
	 * @expectedException \Exception
	 */
	public function testMuxRoutingKeyBadDrone() {
		// given
		$coll = new Collective($this->driver, "test", "default");
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo];
		$vos = ArgParser::marshalArgs($args);
		
		$topic = new TopicConfiguration("test", "default", "what", "myTestMethod");
		
		$req = new Request();
		$req->addHeader("X-routing-key", $topic->getTopicString());
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);

		// then
		$this->assertEquals($args, $resp, 'Unmarshalled vos should match original entities');
	}
	/**
	 * @expectedException \Exception
	 */
	public function testMuxRoutingKeyBadMethod() {
		// given
		$coll = new Collective($this->driver, "test", "default");
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo];
		$vos = ArgParser::marshalArgs($args);
		
		$topic = new TopicConfiguration("test", "default", get_class($this), "dne");
		
		$req = new Request();
		$req->addHeader("X-routing-key", $topic->getTopicString());
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);

		// then
		$this->assertEquals($args, $resp, 'Unmarshalled vos should match original entities');
	}
}
