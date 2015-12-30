<?php

namespace Fliglio\Borg;

use Fliglio\Http\RequestReader;
use Fliglio\Borg\Driver\WireMapper;

class RoutineRequest {

	private $topic;
	private $args;
	private $exitCh;
	private $retryErrors;

	public function __construct(TopicConfiguration $topic, array $args, $exitCh, $retryErrors) {
		$this->topic = $topic;
		$this->args = $args;
		$this->exitCh = $exitCh;
		$this->retryErrors = $retryErrors;
	}

	public function getTopic() {
		return $this->topic;
	}
	public function getArgs() {
		return $this->args;
	}
	public function getExitChan() {
		return $this->exitCh;
	}
	public function getRetryErrors() {
		return $this->retryErrors;
	}
}
