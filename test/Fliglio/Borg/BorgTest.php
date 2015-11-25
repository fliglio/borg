<?php
namespace Fliglio\Borg;



class BorgTest extends \PHPUnit_Framework_TestCase {


	public function setup() {
	}

	public function testBorg() {
		$driver = new RabbitDriver();

		$go = new Scheduler(Demo::class, $driver);

		$demo = new Demo($go);
	}

}
