<?php

use GuzzleHttp\Client;

class ComponentTest extends \PHPUnit_Framework_TestCase {

	private $client;
	private $add;

	public function setup() {
		$this->client = new Client();
		$this->add = sprintf("http://%s:%s", getenv('LOCALDEV_PORT_80_TCP_ADDR'), 80);
	}

	public function testRoundTrip() {
		// given
		$expected = 'hello world';

		// when
		$resp = $this->client->get($this->add."/round-trip?msg=hello");
		
		// then
		$this->assertEquals($expected, $resp->json());
	}

	public function testChanChan() {
		// given
		$expected = 'hello world';

		// when
		$resp = $this->client->get($this->add."/chan-chan?msg=hello");
		
		// then
		$this->assertEquals($expected, $resp->json());
	}

	public function testChanReader() {
		// given
		$expected = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

		// when
		$resp = $this->client->get($this->add."/generate-numbers?limit=10");
	
		// then
		$this->assertEquals($expected, $resp->json());
	}
	
	public function testNullValueInChan() {
		// given
		$expected = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

		// when
		$resp = $this->client->get($this->add."/generate-numbers-2?limit=10");
	
		// then
		$this->assertEquals($expected, $resp->json());
	}
	
}
