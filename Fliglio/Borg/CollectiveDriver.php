<?php

namespace Fliglio\Borg;

interface CollectiveDriver {
	public function go($topic, array $data);
	public function close();
}
