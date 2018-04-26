<?php 

class Facade 
{
	public static function __callStatic($method, $args) {
		echo "call in static";
		$instance = new RouteInstance();

		call_user_func_array('RouteInstance->test', $args);
	}
}

class Route extends Facade
{

}

class RouteInstance
{
	public function test($args)
	{
		var_dump($args);
	}
}

Route::test("hello");