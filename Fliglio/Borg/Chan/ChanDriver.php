<?php

namespace Fliglio\Borg\Chan;

interface ChanDriver {
	public function push(array $data);
	public function get();
	public function close();
}
