<?php

namespace Fliglio\Borg\Driver;

interface ChanReaderDriver {
	public function get(); // [id, entity]
}
