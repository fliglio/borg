<?php

namespace Fliglio\Borg;

interface ChanDriver {
	public function getId();
	public function push(array $data);
	public function get();
	public function close();
}
