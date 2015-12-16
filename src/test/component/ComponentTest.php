<?php

use GuzzleHttp\Client;

class ComponentTest extends \PHPUnit_Framework_TestCase {

	private $client;
	private $add;

	public function setup() {
		$this->client = new Client();
		$this->add = sprintf("http://%s:%s", getenv('LOCALDEV_PORT_80_TCP_ADDR'), 80);
	}

	public function testBorg() {

		$resp = $this->client->get($this->add."/test");

		$this->assertEquals("hello", $resp->json());
	}

}