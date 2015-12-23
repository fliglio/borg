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
}
