<?php

namespace Fliglio\Borg;

use Fliglio\Web\GetParam;

use Fliglio\Borg\BorgImplant;
use Fliglio\Borg\Type\Primitive;
use Fliglio\Borg\Chan\Chan;
use Fliglio\Borg\Chan\ChanReader;


class TestResource {
	use BorgImplant;

	// basic round trip
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

	// recursively determine the fibonacci sequence
	public function fibonacci(GetParam $terms) {
		$ch = $this->mkChan();

		$this->coll()->fibNum($ch, 1, 1, $terms->get());

		$collected = [];
		for ($i = 0; $i < $terms->get(); $i++) {
			$collected[] = $ch->get();
		}
		return $collected;
	}
	public function fibNum(Chan $ch, $a, $b, $terms) {
		$ch->add($a);
		if ($terms <= 10) {
			$this->coll()->fibNum($ch, $b, $a+$b, $terms--);
		}
	}

	// Gregory-Leibniz series
	public function pi(GetParam $terms) {
		$ch = $this->mkChan();

		$this->coll()->piTerm($ch, 1, $terms->get());

		$pi = 3;

		for ($i = 0; $i < $terms->get(); $i++) {
			$pi += $ch->get();
		}
		return $pi;
	}
	public function piTerm(Chan $ch, $termIdx, $terms) {
		if ($termIdx != $terms) {
			$this->coll()->piTerm($ch, $termIdx+1, $terms);
		}
		$base = $termIdx * 4 - 2;
		$term = 4 / ($base * ($base + 1) * ($base + 2));
		$term += -4 / (($base + 2) * ($base + 3) * ($base + 4));

		$ch->add($term);
	}

}
