<?php

print_r(fibonacci(50));

function fibonacciRecursive($m)
{
	if ($m <=1) {
		return $m;
	}
	//每次都会再生成两个新的fibonacci，时间复杂度2^n.不建议用这种。对于$m=100.程序崩溃
	return fibonacciRecursive($m-1) + fibonacciRecursive($m-2);
}

function fibonacci($m)
{
	$fibonacciArray[0] = 0;
	$fibonacciArray[1] = 1;
	for ($i=2;$i<$m;$i++) {
		$fibonacciArray[$i] = $fibonacciArray[$i-1] + $fibonacciArray[$i-2];
	}
	return $fibonacciArray;
}