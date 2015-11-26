<?php

namespace Fliglio\Borg\Routing;

use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Http\RequestReader;
use Fliglio\Routing\Type\StaticRoute;

class BorgRoute extends StaticRoute {
	
	private $collective;




	public function getResourceInstance() {
		return $this->collective->getInstance();
	}
	public function getResourceMethod(RequestReader $r) {
		$topic = $r->getHeader("X-routing_key");
		$parts = split(".", $topic);
		return array_pop($parts);
	}

}

