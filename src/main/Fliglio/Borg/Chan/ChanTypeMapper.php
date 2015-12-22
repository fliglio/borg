<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Web\MappableApi;

use Fliglio\Borg\Type\Primitive;
use Fliglio\Borg\CollectiveDriver;
use Fliglio\Borg\ArgParser;

class ChanTypeMapper {

	private $type; // chan type or null for primitive
	private $factory;

	/**
	 * Manage marshalling and unmarshalling entities for the ChanDriver
	 *
	 * @param $type     Chan Type: null for primitive, or implement MappableApi,
	 *                  or be a Chan (special because it needs CollectiveDriver
	 *                  to unmarshal)
	 * @param $factory  factory to create driver to back chanel's cross
	 *                  process persistence
	 */
	public function __construct($type, CollectiveDriver $factory) {
		switch (true) {
		case is_null($type):
		case in_array('Fliglio\Web\MappableApi', class_implements($type)):
		case $type == Chan::CLASSNAME:
			$this->type = $type;
			break;
		default:
			throw new \Exception(sprintf("Type '%s' isn't marshallable", $type));
		}
		
		$this->factory = $factory;
	}

	public function getType() {
		return $this->type;
	}

	public function marshal($entity) {
		if (is_null($this->type)) {
			if (is_object($entity)) {
				throw new \Exception("This Chan expects a primitive");
			}
		} else if (!is_object($entity) || !is_a($entity, $this->type)) {
			throw new \Exception(sprintf("This Chan expects a %s", $this->type));
		}
		return ArgParser::marshalArg($entity);
	}

	public function unmarshal($vo) {
		return ArgParser::unmarshalArg($this->factory, $this->type, $vo);
	}
}
