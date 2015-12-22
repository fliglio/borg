<?php

namespace Fliglio\Borg;

class TopicConfiguration {

	private $ns;
	private $dc;
	private $type;
	private $method;

	public function __construct($ns, $dc, $drone, $method) {
		$this->ns = $ns;
		$this->type = is_string($drone) ? $drone : get_class($drone);
		$this->dc = $dc;
		$this->method = $method;
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
		error_log("FROM STR: ".$str);
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
