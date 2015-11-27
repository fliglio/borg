<?php

namespace Fliglio\Borg;

interface CollectiveDriver {
	public function createChan($id = null);
	public function go($topic, array $data);
	public function close();
}
