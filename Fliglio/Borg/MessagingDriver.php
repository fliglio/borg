<?php

namespace Fliglio\Borg;

interface MessagingDriver {
	public function go($type, $method, array $data);
}
