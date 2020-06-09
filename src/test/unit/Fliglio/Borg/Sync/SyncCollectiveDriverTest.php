<?php


namespace unit\Fliglio\Borg\Sync;


use Fliglio\Borg\BorgImplant;
use Fliglio\Borg\Collective;
use Fliglio\Borg\Mapper\DefaultMapper;
use Fliglio\Borg\RoutingConfiguration;
use Fliglio\Borg\Sync\SyncCollectiveDriver;

class SyncCollectiveDriverTest extends \PHPUnit_Framework_TestCase {
	use BorgImplant;

	private $capturedValue;

	public function testSyncCollectiveDriver_default() {
		// given
		$driver  = new SyncCollectiveDriver();
		$mapper  = new DefaultMapper($driver);
		$routing = new RoutingConfiguration("sync-test");

		$coll = new Collective($driver, $mapper, $routing);
		$coll->assimilate($this);

		$driver->setCollective($coll); // driver needs ref to collective for synchronously calling `mux`

		$expected = "something something...";

		// when
		$this->coll()->doSomethingSynchronously($expected);

		// then
		$this->assertEquals($expected, $this->capturedValue);

	}

	public function doSomethingSynchronously($value) {
		$this->capturedValue = $value;
	}
}