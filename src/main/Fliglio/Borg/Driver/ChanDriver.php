<?php

namespace Fliglio\Borg\Driver;

/**
 * Interface for Chan persistence implementation
 * (such as with rabbitmq)
 */
interface ChanDriver {
	public function getId();
	public function add($data);
	public function get();
	public function close();
}
