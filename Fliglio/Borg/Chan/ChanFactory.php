<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Borg\MessagingDriver;

class ChanFactory {

	private $fac;

	public function __construct(ChanDriverFactory $fac) {
		$this->fac = $fac;
	}

	public function mkchan($type) {
		return new Chan($type, $fac);
	}
}

