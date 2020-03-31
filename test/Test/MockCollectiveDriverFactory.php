<?php

namespace Fliglio\Borg\Test;

class MockCollectiveDriverFactory extends \PHPUnit_Framework_TestCase {
	private static $driver;

	public static $q = [];

	public static function get() {
		if (!isset(self::$driver)) {
			$i = new self();
			self::$driver = $i->create();
		}
		return self::$driver;
	}

	public function create() {
		$driver = $this->getMockBuilder('\Fliglio\Borg\Amqp\AmqpCollectiveDriver')
			->disableOriginalConstructor()
			->getMock();
		
		$driver->method('createChan')
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
						return array_shift(MockCollectiveDriverFactory::$q);
					}));
				$chanDriver->method('add')
					->will($this->returnCallback(function($vo) {
						array_push(MockCollectiveDriverFactory::$q, $vo);
					}));

				return $chanDriver;
			}));
		return $driver;
	}

}