<?php

use GuzzleHttp\Client;

class ShakespeareTest extends \PHPUnit_Framework_TestCase {

	private $client;
	private $add;

	public function setup() {
		$this->client = new Client();
		$this->add = sprintf("http://%s:%s", getenv('LOCALDEV_PORT_80_TCP_ADDR'), 80);
	}

	public function testNumResults() {
		// given
		$expected = 28565;

		// when
		$resp = $this->client->get($this->add."/shakespeare/words");
		
		// then
		$this->assertEquals($expected, count($resp->json()));
	}

}

