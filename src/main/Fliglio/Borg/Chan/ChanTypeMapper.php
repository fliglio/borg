<?php

namespace Fliglio\Borg\Chan;

use Fliglio\Web\MappableApi;

use Fliglio\Borg\CollectiveDriver;
use Fliglio\Borg\ArgParser;
use Fliglio\Borg\Type\TypeUtil;
use Fliglio\Borg\Type\ArgMapper;

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
		if (!TypeUtil::isMarshallableType($type)) {
			throw new \Exception(sprintf("Type '%s' isn't marshallable", $type));
		}
		$this->type = $type;
		
		$this->factory = $factory;
	}

	public function getType() {
		return $this->type;
	}

	public function marshal($entity) {
		if (!TypeUtil::isA($entity, $this->type)) {
			throw new \Exception(sprintf("This Chan expects a %s", $this->type));
		}
		return ArgMapper::marshalArg($entity);
	}

	public function unmarshal($vo) {
		return ArgMapper::unmarshalArg($this->factory, $this->type, $vo);
	}
}
