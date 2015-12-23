<?php

namespace Fliglio\Borg\Driver;

interface WireMapper {
	public function marshalForMethod(array $args, $inst, $method);
	public function unmarshalForMethod(array $vos, $inst, $method);
	public function marshalArg($arg, $type);
	public function unmarshalArg($vo, $type);

}
