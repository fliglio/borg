<?php

namespace Fliglio\Borg;

class TopicConfiguration {

	private $ns;
	private $dc;
	private $type;
	private $method;

	public function __construct($ns, $dc, $drone, $method) {
		$this->ns = $this->validate($ns);
		$this->type = $this->validate(is_string($drone) ? $drone : get_class($drone));
		$this->dc = $this->validate($dc);
		$this->method = $this->validate($method);
	}

	private function validate($str) {
		if (strpos($str, '.') !== false) {
			throw new \Exception(sprintf("TopicConfiguration component cannot have '.': '%s'", $str));
		}
		return $str;
	}

	public function getNs() {
		return $this->ns;
	}
	public function getDc() {
		return $this->dc;
	}
	public function getType() {
		return $this->type;
	}
	public function getMethod() {
		return $this->method;
	}


	public static function fromTopicString($str) {
		$parts = explode(".", $str);

		$method = array_pop($parts);
		$ns = array_shift($parts);
		$dc = array_shift($parts);
		$type = implode("\\", $parts);

		return new self($ns, $dc, $type, $method);
	}

	public function getTopicString() {
	
		$topicClass = str_replace("\\", ".", $this->type);
		return $this->ns . '.' . $this->dc . '.' . $topicClass . '.' . $this->method;
	}

}
