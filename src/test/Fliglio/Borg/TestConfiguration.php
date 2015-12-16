<?php

namespace Fliglio\Borg;

use Fliglio\Http\Http;
use Fliglio\Routing\Type\RouteBuilder;
use Fliglio\Fli\Configuration\DefaultConfiguration;

use GuzzleHttp\Client;

class TestConfiguration extends DefaultConfiguration {


	protected function getTestResource() {
		return new TestResource();
	}

	public function getRoutes() {

		$resource = $this->getTestResource();

		return [
			RouteBuilder::get()
				->uri('/test')
				->resource($resource, 'test')
				->method(Http::METHOD_GET)
				->build(),
					
		];
	}

}


