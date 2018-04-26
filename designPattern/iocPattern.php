<?php

class Container 
{
	protected $binds;
	protected $instances;
	
	public function bind($abstract, $concrete) {
		if ($concrete instanceof closure) {
			$this->binds[$abstract] = $concrete;
		} else {
			$this->instances[$abstract] = $concrete;
		}
	}
	
	public function make($abstract, $parameters = []) {
		if (isset($this->instance[$abstract])) {
			return $this->$instance[$abstract];
		}
		array_unshift($parameters, $this);
		return call_user_func_array($this->binds[$abstract], $parameters);
	}
}
class Flight 
{
	public $height;
	public function __construct($height) {
		echo "Flight module is made" . '<br>';
		$this->height = $height;
	}
}

class Shot 
{
	public $speed;
	public function __construct($speed) {
		echo "Shot module is made" . '<br>';
		$this->speed = $speed;
	}
}

class SuperMan 
{
	protected $module;
	public function __construct($module) {
		$this->module = $module;
	}
}

$container = new Container();
$container->bind('Flight', function ($container) {
	return new Flight(50); 
});
$container->bind('Shot', function ($container) {
	return new Shot(100); 
});
$container->bind('SuperMan', function ($container, $moduleName) {
	return new SuperMan($container->make($moduleName));
});

$superMan1 = $container->make('SuperMan', ['Flight']);
//$superMan2 = $container->make('SuperMan', ['Shot']);
var_dump($superMan1);





















