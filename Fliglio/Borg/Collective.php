<?php

namespace Fliglio\Borg;


class Collective {

	private $agents = [];
	private $driver;

	public function __construct(MessagingDriver $driver) {
		$this->driver = $driver;
	}
	
	public function addCollectiveAgent($i) {
		$this->agents[] = $i;
	}

	public function getCollectiveAgent($type) {
		foreach ($this->agents as $agent) {
			if ($type == get_class($agent)) {
				return $agent;
			}
		}
		throw new \Exception("agent ".$type."not found");
	}
	public function getCollectiveAgents() {
		return $this->agents;
	}

	public function dispatch($collectiveAgent, $method, array $args) {

		$data = [];

		foreach ($args as $arg) {
			if (!in_array('Fliglio\Web\MappableApi', class_implements($arg))) {
				throw new \Exception($entityType . " doesn't implement Fliglio\Web\MappableApi");
			}
			$data[] = $arg->marshal();
		}

		$className = get_class($collectiveAgent);
		$topicBase = str_replace("\\", ".", $className);
		$this->driver->go($topicBase, $method, $data);
	}
}
