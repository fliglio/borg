<?php

namespace Fliglio\Borg;

use Fliglio\Fli\DefaultResolverApp;
use Fliglio\Fli\ResolverAppMux;

class TestApplication extends ResolverAppMux {
	public function __construct(TestConfiguration $cfg) {
		parent::__construct();

		$fli = new DefaultResolverApp();
		$fli->configure($cfg);

		$this->addApp($fli);
	}

}
