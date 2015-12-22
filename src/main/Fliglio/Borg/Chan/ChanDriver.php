<?php

namespace Fliglio\Borg\Chan;

/**
 * Interface for Chan persistence implementation
 * (such as with rabbitmq)
 */
interface ChanDriver {
	public function getId();
	public function add($data);
	public function get($noBlock=false);
	public function close();
}
