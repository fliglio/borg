<?php

namespace Fliglio\Borg;

class RabbitDriver implements MessagingDriver {

	
	public function createChanDriver() {
		return new RabbitChanDriver();
	}

	public function go($type, $method, array $data) {
	
	}
}
