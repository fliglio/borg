<?php

namespace Fliglio\Borg\Chan;

interface ChanDriver {
	public function getId();
	public function add(array $data);
	public function get();
	public function close();
}
