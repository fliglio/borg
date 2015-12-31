<?php
namespace Fliglio\Borg;

use Fliglio\Flfc\Request;
use Fliglio\Borg\Mapper\DefaultMapper;
use Fliglio\Borg\Test\MockCollectiveDriverFactory;

class TopicTest extends \PHPUnit_Framework_TestCase {

	private $driver;
	private $mapper;

	public function setup() {
		$this->driver = MockCollectiveDriverFactory::get();
		$this->mapper = new DefaultMapper($this->driver);
	}
	private function buildRequest($ns, $dc, $type, $method) {
		$ex = new Chan(null, $this->driver, $this->mapper);
		return (new RoutineRequestBuilder())
			->ns($ns)
			->dc($dc)
			->type($type)
			->method($method)
			->args([])
			->exitChan($ex)
			->retryErrors(false)
			->build();
	}

	public function testTopic() {
		// given
		$req = $this->buildRequest('foo', 'bar', 'Fliglio\Borg\TopicTest', 'testTopic');
		$expected = 'foo.bar.Fliglio.Borg.TopicTest.testTopic';

		// when
		$r = $this->mapper->marshalRoutineRequest($req);

		// then
		$this->assertEquals($expected, $r->getHeader('X-routing-key'), 'topic config should build expected string');
	}


	public function testTopicFromString() {
		// given
		$x = $this->buildRequest('', '', 'Fliglio\Borg\TopicTest', 'testTopic');
		$x2 = $this->mapper->marshalRoutineRequest($x);

		$req = new Request();
		$req->addHeader("X-routing-key", 'foo.bar.Fliglio.Borg.TopicTest.testTopic');
		$req->setBody($x2->getBody());

		// when
		$found = $this->mapper->unmarshalRoutineRequest($req);

		// then
		$this->assertEquals('foo', $found->getNs(), 'should match');
		$this->assertEquals('bar', $found->getDc(), 'should match');
		$this->assertEquals('Fliglio\Borg\TopicTest', $found->getType(), 'should match');
		$this->assertEquals('testTopic', $found->getMethod(), 'should match');
	}

	
	/**
	 * @expectedException \Exception
	 */
	public function testBadTopicComponentNs() {
		// when
		$req = $this->buildRequest('fo.o', 'bar', 'Fliglio\Borg\TopicTest', 'testTopic');
	}
	/**
	 * @expectedException \Exception
	 */
	public function testBadTopicComponentDc() {
		// when
		$req = $this->buildRequest('foo', 'ba.r', 'Fliglio\Borg\TopicTest', 'testTopic');
	}
	/**
	 * @expectedException \Exception
	 */
	public function testBadTopicComponentType() {
		// when
		$req = $this->buildRequest('foo', 'ba.r', 'Fliglio\Borg\Notaclass', 'testTopic');
		$this->mapper->marshalRoutineRequest($req);
	}
	/**
	 * @expectedException \Exception
	 */
	public function testBadTopicComponentMethod() {
		// when
		$req = $this->buildRequest('foo', 'ba.r', 'Fliglio\Borg\TopicTest', 'notamethod');
		$this->mapper->marshalRoutineRequest($req);
	}

}

