<?php

namespace Fliglio\Borg;

interface MessagingDriver {
	public function createChanDriver();
	public function go($type, $method, array $data);
}
