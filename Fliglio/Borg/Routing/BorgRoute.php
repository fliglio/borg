<?php

namespace Fliglio\Borg\Routing;

use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Http\RequestReader;
use Fliglio\Routing\Type\StaticRoute;
use Fliglio\Borg\Collective;

class BorgRoute extends StaticRoute {
	
	private $collective;

	public function setCollective(Collective $c) {
		$this->collective = $c;
	}
	public function getCollective() {
		return $this->collective;
	}


	public function getResourceInstance() {
		return $this->getCollective()->getInstance();
	}
	public function getResourceMethod(RequestReader $r) {
		if ($r->isHeaderSet("X-routing_key")) {
			$topic = $r->getHeader("X-routing_key");
			$parts = explode(".", $topic);
			return array_pop($parts);
		}
		throw new \Exception("x-routing_key not set");
	}

}

