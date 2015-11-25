<?php

namespace Fliglio\MessagingDriver;

interface MessagingDriver {
	public function createChanDriver();
	public function go($type, $method, array $data);
}
