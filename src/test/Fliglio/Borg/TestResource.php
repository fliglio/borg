<?php

namespace Fliglio\Borg;

use Fliglio\Web\GetParam;

use Fliglio\Borg\BorgImplant;
use Fliglio\Borg\Type\Primitive;
use Fliglio\Borg\Chan\Chan;
use Fliglio\Borg\Chan\ChanReader;


class TestResource {
	use BorgImplant;

	
	public function test(GetParam $msg) {
		error_log("in test");
		$ch = $this->mkChan();
		$this->coll()->worldAdder($msg->get(), $ch);

		return $ch->get();
	}
	public function worldAdder($msg, Chan $ch) {
		error_log("in world adder");
		$ch->add($msg . " world");
	}


}
