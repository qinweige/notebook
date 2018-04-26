<?php

class ModuleFactory 
{
	//工厂类实现了统一创建模块的能力，解耦。但是想增加新的模块，还是需要更改工厂类
	//IOC中将模块创建用回调函数new出来（bind上参数），所以不需要工厂类。
	public function makeModule($module, $parameters) {
		//注意switch写法
		switch ($module) {
			case 'Flight': 
				return new Flight($parameters[0]);
				break;
			case 'Shot': 
				return new Shot($parameters[0]);
				break;
			default: return;
		}
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

class superMan
{
	public $power;
	public function __construct($modules) {
		$factory = new ModuleFactory();
		//实现我们只传递需要的名称和参数，即可创建
		//否则，我们将一个一个创建，例如： makeModule('Flight', [100]);
		foreach ($modules as $module=>$parameters) {
			$this->power[] = $factory->makeModule($module, $parameters);
		}
	}
}

//提供所有需要创建的模块内容，由工厂统一按照名称和参数创建
$modules = ['Flight' => [50], 'Shot' => [100]];
$superMan = new superMan($modules);
var_dump($superMan->power);

//工厂模式注重对对象的创建，装饰者模式则是对一堆过程的组织。


















	