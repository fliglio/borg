<?php

use Fliglio\Borg\TestApplication;
use Fliglio\Borg\TestConfiguration;

error_reporting(E_ALL | E_STRICT);
ini_set("display_errors" , 1);

require_once __DIR__ . '/../../../vendor/autoload.php';

try {
	$svc = new TestApplication(new TestConfiguration());
	$svc->run();
} catch (\Exception $e) {
	error_log($e);
	http_response_code(500);
}