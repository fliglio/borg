<?php

namespace Fliglio\Borg\Routing;

use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Routing\Type\ROuteBuilder;
use Fliglio\Borg\Collective;

class BorgRouteBuilder extends RouteBuilder {
	const TYPE_PATTERN = 0;
	const TYPE_STATIC = 1;
	const TYPE_ALL = 2;
	const TYPE_NONE = 3;

	private $command = null;

	private $resource = null;
	private $resourceMethod = null;

	private $uriTemplate = "";
	private $routeType = null;
	private $protocol = null;
	private $methods = array();
	private $key = null;
	private $params = array();

	private $collective;

	public function __construct() {}
	public static function get() {
		return new self();
	}
	public function collective(Collective $c) {
		$this->collective = $c;
		return $this;
	}

	public function resource($resource, $method) {
		$this->resource = $resource;
		$this->resourceMethod = $method;
		return $this;
	}
	public function command($cmd) {
		list($ns, $name, $methodName) = explode('.', $cmd);
		
		$className = $ns . '\\' . $name;
		
		$instance = new $className();
		return $this->resource($instance, $methodName);
	}
	public function protocol($protocol) {
		$this->protocol = $protocol;
		return $this;
	}
	public function key($key) {
		$this->key = $key;
		return $this;
	}
	public function catchAll() {
		$this->routeType = self::TYPE_ALL;
		return $this;
	}
	public function catchNone() {
		$this->routeType = self::TYPE_NONE;
		return $this;
	}

	public function uri($uriTemplate) {
		$this->uriTemplate = $uriTemplate;
		if (strPos($uriTemplate, ':') === false) {
			$this->routeType = self::TYPE_STATIC;
		} else {
			$this->routeType = self::TYPE_PATTERN;
		}
		return $this;
	}

	public function method($type) {
		$this->methods[] = $type;
		return $this;
	}

	public function param($key, $val) {
		$this->params[$key] = $val;
		return $this;
	}

	public function build() {
		$route = new BorgRoute($this->uriTemplate, $this->params);
		$route->setCollective($this->collective);
		
		$route->setKey($this->key);
		$route->setProtocol($this->protocol);


		if (!empty($this->methods)) {
			$route->setMethods($this->methods);
		}

		return $route;
	}
}

