<?php

namespace Fliglio\Borg\Chan;

interface ChanDriverFactory {
	public function create($id = null);
}
