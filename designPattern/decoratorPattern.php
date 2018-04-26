<?php 

interface MiddleWare 
{
	//外观模式。和外界打交道的函数，由这个函数串联起所有的过程
	public static function handle($request, closure $next);
}

class convertLength implements MiddleWare {
	//第一个接入的外观，做一些工作
	public static function handle($request, closure $next) {
		echo '<br>' . "Convert length ";
		$request = str_pad($request, 10, 'o');
		echo $request;
		$next($request);
	}
}

class reverseString implements MiddleWare {
	//第二个接入的外观，继续做一些工作
	public static function handle($request, closure $next) {
		echo '<br>' . "Reserve the string " . $request;
		$request = strrev($request);
		$next($request);
	}
}

//所有模块的一个数组
$pipes = ['convertLength', 'reverseString'];
$pipes = array_reverse($pipes);
$originNextHandle = function ($request) {
	echo '<br>' . $request;
};

//返回闭包函数，包裹每一个模块的处理请求，最后一个一个打开处理
function wrapAllHandles() {
	return function($stack, $pipe) {
		return function($request) use ($stack, $pipe) {
			return $pipe::handle($request, $stack);
		};
	};
}

$request = 'world';
$closureForAll = array_reduce($pipes, wrapAllHandles(), $originNextHandle);
//打开处理，并且传递request参数
$closureForAll($request);

