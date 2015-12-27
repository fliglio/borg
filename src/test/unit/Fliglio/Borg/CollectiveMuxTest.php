<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Api\Foo;
use Fliglio\Flfc\Request;
use Fliglio\Borg\Mapper\DefaultMapper;

class CollectiveMux extends \PHPUnit_Framework_TestCase {
	use BorgImplant;

	private $driver;
	private $mapper;
	private $routing;
		
	public $msg;
	public $ch;
	public $foo;
	public $optArg;
	const OPT_ARG_DEFAULT = "I'm The Default";

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

		$this->routing = new RoutingConfiguration("borg-demo");
		$this->mapper = new DefaultMapper($this->driver);

		$this->msg = "hello world";
		$this->ch = new Chan(null, $this->driver, $this->mapper);
		$this->foo = new Foo("bar");
		$this->optArg = "hello";
	}

	public function myTestMethod($msg, Chan $ch, Foo $foo, $optArg = self::OPT_ARG_DEFAULT) {
		return [$msg, $ch, $foo, $optArg];
	}

	public function testMux() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo, $this->optArg];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		
		$topic = new TopicConfiguration("test", "default", get_class($this), "myTestMethod");
		
		$req = new Request();
		$req->addHeader("X-routing-key", $topic->getTopicString());
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);

		// then
		$this->assertEquals($args, $resp, 'Unmarshalled vos should match original entities');
	}
	public function testMuxWithDefault() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo];
		$expected =  [$this->msg, $this->ch, $this->foo, self::OPT_ARG_DEFAULT];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		
		$topic = new TopicConfiguration("test", "default", get_class($this), "myTestMethod");
		
		$req = new Request();
		$req->addHeader("X-routing-key", $topic->getTopicString());
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);

		// then
		
		$this->assertEquals($expected, $resp, 'Unmarshalled vos should match original entities plus the default for the optional arg');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testMuxNoRoutingKey() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		
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
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		
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
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [$this->msg, $this->ch, $this->foo];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		
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
