<?php

namespace Fliglio\Borg;

interface MessagingDriver {
	// goroutine
	public function go($topic, array $data);
	public function close();

	// chan
	public function push($id, array $data);
	public function get($id);
	public function closeChan($id);
}
