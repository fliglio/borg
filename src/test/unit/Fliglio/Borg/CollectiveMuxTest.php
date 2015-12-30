<?php
namespace Fliglio\Borg;

use Fliglio\Borg\Api\Foo;
use Fliglio\Flfc\Request;
use Fliglio\Borg\Mapper\DefaultMapper;
use Fliglio\Borg\Test\MockCollectiveDriverFactory;

class CollectiveMux extends \PHPUnit_Framework_TestCase {
	use BorgImplant;

	private $driver;
	private $mapper;
	private $routing;
		
	public $optArg;
	const OPT_ARG_DEFAULT = "I'm The Default";

	public function setup() {
		$this->driver = MockCollectiveDriverFactory::get();
		$this->routing = new RoutingConfiguration("borg-demo");
		$this->mapper = new DefaultMapper($this->driver);
	}

	public function myTestMethod($msg, Chan $ch, Foo $foo, $optArg = self::OPT_ARG_DEFAULT) {
		return [$msg, $ch, $foo, $optArg];
	}

	public function testMux() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
			"hello",
		];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		// add in exit chan
		$vos[] = $this->mapper->marshalArg(new Chan(null, $this->driver, $this->mapper), Chan::CLASSNAME);
		$vos[] = false;

		$topic = new TopicConfiguration("test", "default", get_class($this), "myTestMethod");

		$req = new Request();
		$req->addHeader("X-routing-key", $topic->getTopicString());
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);

		// then
		$this->assertEquals($args, $resp, 'Unmarshalled vos should match original entities');
	}
	public function testMuxWithDefaultParameter() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];
		$expected = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
			self::OPT_ARG_DEFAULT,
		];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		// add in exit chan
		$vos[] = $this->mapper->marshalArg(new Chan(null, $this->driver, $this->mapper), Chan::CLASSNAME);
		$vos[] = false;

		
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
		
		$args = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		// add in exit chan
		$vos[] = $this->mapper->marshalArg(new Chan(null, $this->driver, $this->mapper), Chan::CLASSNAME);
		$vos[] = false;
		
		$topic = new TopicConfiguration("test", "default", get_class($this), "myTestMethod");
		
		$req = new Request();
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);
	}
	
	/**
	 * @expectedException \Exception
	 */
	public function testMuxRoutingKeyBadDrone() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		// add in exit chan
		$vos[] = $this->mapper->marshalArg(new Chan(null, $this->driver, $this->mapper), Chan::CLASSNAME);
		$vos[] = false;
		
		$topic = new TopicConfiguration("test", "default", "what", "myTestMethod");
		
		$req = new Request();
		$req->addHeader("X-routing-key", $topic->getTopicString());
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);
	}
	/**
	 * @expectedException \Exception
	 */
	public function testMuxRoutingKeyBadMethod() {
		// given
		$coll = new Collective($this->driver, $this->mapper, $this->routing);
		$coll->assimilate($this);
		
		$args = [
			"hello world",
			new Chan(null, $this->driver, $this->mapper),
			new Foo("bar"),
		];
		$vos = $this->mapper->marshalForMethod($args, $this, 'myTestMethod');
		// add in exit chan
		$vos[] = $this->mapper->marshalArg(new Chan(null, $this->driver, $this->mapper), Chan::CLASSNAME);
		$vos[] = false;
		
		$topic = new TopicConfiguration("test", "default", get_class($this), "dne");
		
		$req = new Request();
		$req->addHeader("X-routing-key", $topic->getTopicString());
		$req->setBody(json_encode($vos));

		// when
		$resp = $coll->mux($req);
	}
}
