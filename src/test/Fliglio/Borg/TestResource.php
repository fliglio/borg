<?php

namespace Fliglio\Borg;

use Fliglio\Web\GetParam;

use Fliglio\Borg\BorgImplant;
use Fliglio\Borg\Chan;
use Fliglio\Borg\ChanReader;


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

	// find all prime numbers up to a limit
	public function prime(GetParam $limit) {
		$ch = $this->mkChan();
		$ex = $this->mkChan();

		for ($i = 2; $i < $limit->get(); $i++) {
			$this->collectIfPrime($ch, $ex,  $i);
		}

		$primes = [];
		$r = new ChanReader([$ch, $ex]);
		$exits = 0;
		while ($exits+2 < $limit->get()) {
			list($id, $val) = $r->get();
			switch ($id) {
			case $ch->getId():
				$primes[] = $val;
			case $ex->getId():
				$exits++;
			}
		}
		return $primes;
	}
	public function collectIfPrime(Chan $ch, Chan $ex, $n) {
		for ($i = 2; $i < $n; $i++) {
			if ($n % $i == 0) {
				$ex->add(true);
				return;
			}
		}
		
		$ch->add($n);
		$ex->add(true);
	}

}
