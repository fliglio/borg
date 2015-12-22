<?php
namespace Fliglio\Borg;


class TopicTest extends \PHPUnit_Framework_TestCase {


	public function setup() {
	}

	public function testTopic() {
		// given
		$cfg = new TopicConfiguration('foo', 'bar', 'baz', 'testTopic');
		$expected = 'foo.bar.baz.testTopic';

		// when
		$str = $cfg->getTopicString();

		// then
		$this->assertEquals($expected, $str, 'topic config should build expected string');
	}

	public function testTopicWithInstance() {
		// given
		$cfg = new TopicConfiguration('foo', 'bar', 'Fliglio\Borg\TopicTest', 'testTopic');
		$expected = 'foo.bar.Fliglio.Borg.TopicTest.testTopic';

		// when
		$instCfg = new TopicConfiguration('foo', 'bar', $this, 'testTopic');

		// then
		$this->assertEquals($cfg, $instCfg, 'constructing with classname or instance should build same object');
		$this->assertEquals($expected, $cfg->getTopicString(), 'topic config should build expected string');
		$this->assertEquals($expected, $instCfg->getTopicString(), 'topic config should build expected string');
	}

	public function testTopicFromString() {
		// given
		$expected = new TopicConfiguration('foo', 'bar', 'baz', 'testTopic');
		$topicStr = 'foo.bar.baz.testTopic';

		// when
		$cfg = TopicConfiguration::fromTopicString($topicStr);

		// then
		$this->assertEquals($expected, $cfg, 'topic configs should match');
	}

	public function testTopicToAndFromShouldBeSymmetrical() {
		// given
		$cfg = new TopicConfiguration('foo', 'bar', 'baz', 'testTopic');

		// when
		$topicStr = $cfg->getTopicString();
		$cfg2 = TopicConfiguration::fromTopicString($topicStr);

		// then
		$this->assertEquals($cfg, $cfg2, 'topic configs should match');
	}

}

