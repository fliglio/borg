<?php

namespace Fliglio\Borg\Chan;

interface ChanDriver {
	public function getId();
	public function add($data);
	public function get();
	public function close();
}
