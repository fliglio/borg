<?php

namespace Fliglio\Borg\Chan;

interface ChanDriver {
	public function getId();
	public function add($data);
	public function get($noBlock=false);
	public function close();
}
