<?php

namespace Fliglio\Borg;

use Fliglio\Web\GetParam;
use Fliglio\Borg\BorgImplant;

class TestResource {
	use BorgImplant;

	// basic round trip
	public function roundTrip(GetParam $msg) {
		$ch = $this->coll()->mkChan();
		$this->coll()->worldAdder($msg->get(), $ch);

		return $ch->get();
	}
	public function worldAdder($msg, Chan $ch) {
		$ch->add($msg . " world");
	}

	// use a chan of type chan
	public function chanChan(GetParam $msg) {
		$exit = $this->coll()->mkChan();
		$ch = $this->coll()->mkChan(Chan::CLASSNAME);
		$this->coll()->chanAdder($ch, $exit);
	
		$ch2 = $ch->get();
		$ch2->add($msg->get());

		return $exit->get();
	}
	public function chanAdder(Chan $ch, Chan $exit) {
		$ch2 = $this->coll()->mkChan();
		$ch->add($ch2);
		
		$exit->add($ch2->get() . " world");
	}

	// test ChanReader
	public function generateNumbers(GetParam $limit) {
		$ch = $this->coll()->mkChan();
		$ex = $this->coll()->mkChan();

		$this->coll()->gen($ch, $ex, $limit->get());
		
		$nums = [];

		$r = $this->coll()->mkChanReader([$ch, $ex]);
		while (true) {
			list($id, $val) = $r->get();
			switch ($id) {
			case $ch->getId():
				$nums[] = $val;
				break;
			case $ex->getId():
				return $nums;
			}
		}
	}
	public function gen(Chan $ch, Chan $ex, $limit) {
		for ($i = 0; $i <= $limit; $i++) {
			$ch->add($i);
		}
		sleep(1); // there's a race condition here, not good!
		$ex->add(true);
	}
	
	// test Passing null
	public function generateNumbersTwo(GetParam $limit) {
		$ch = $this->coll()->mkChan();

		$this->coll()->genTwo($ch, $limit->get());
		
		$nums = [];

		while (true) {
			$num = $ch->get();
			if (is_null($num)) {
				return $nums;
			}
			$nums[] = $num;
		}
	}
	public function genTwo(Chan $ch, $limit) {
		for ($i = 0; $i <= $limit; $i++) {
			$ch->add($i);
		}
		$ch->add(null);
	}



}