<?php

namespace Fliglio\Borg\Driver;

use Fliglio\Http\RequestReader;
use Fliglio\Borg\RoutineRequest;

interface WireMapper {
	public function marshalRoutineRequest(RoutineRequest $req); // RequestReader
	public function unmarshalRoutineRequest(RequestReader $req); // RoutineRequest
	public function marshalForMethod(array $args, $inst, $method);
	public function unmarshalForMethod(array $vos, $inst, $method);
	public function marshalArg($arg, $type);
	public function unmarshalArg($vo, $type);

}
