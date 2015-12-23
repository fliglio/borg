<?php

namespace Fliglio\Borg;

use Fliglio\Web\GetParam;

use Fliglio\Borg\BorgImplant;


class TestResource {
	use BorgImplant;

	// use a chan of type chan
	public function chanChan(GetParam $msg) {
		$exit = $this->mkChan();
		$ch = $this->mkChan(Chan::CLASSNAME);
		$this->coll()->chanAdder($ch, $exit);
	
		$ch2 = $ch->get();
		$ch2->add($msg->get());

		return $exit->get();
	}
	public function chanAdder(Chan $ch, Chan $exit) {
		$ch2 = $this->mkChan();
		$ch->add($ch2);
		
		$exit->add($ch2->get() . " world");
	}

	// basic round trip
	public function test(GetParam $msg) {
		$ch = $this->mkChan();
		$this->coll()->worldAdder($msg->get(), $ch);

		return $ch->get();
	}
	public function worldAdder($msg, Chan $ch) {
		$ch->add($msg . " world");
	}

	// test ChanReader
	public function generateNumbers(GetParam $limit) {
		$ch = $this->mkChan();
		$ex = $this->mkChan();

		$this->coll()->gen($ch, $ex, $limit->get());
		
		$nums = [];
		sleep(1); // not good...

		$r = $this->coll()->mkChanReader([$ch, $ex]);
		while (true) {
			list($id, $val) = $r->get();
			switch ($id) {
			case $ch->getId():
				error_log(">>>FOUND: ".$id);
				$nums[] = $val;
				break;
			case $ex->getId():
				error_log(">>>EXIT: ".$id);
				return $nums;
			}
		}
	}
	public function gen(Chan $ch, Chan $ex, $limit) {
		for ($i = 0; $i <= $limit; $i++) {
			$ch->add($i);
			error_log($i);
		}

		$ex->add(true);
		error_log("done");
	}
}
