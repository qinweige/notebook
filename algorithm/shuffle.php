<?php

$arr = [2,3,4,5,6,7,8,9];
print_r(shuffleRewrite($arr));

//这边不能用shuffle，因为是内置函数。
function shuffleRewrite($arr)
{
	$length = count($arr);
	for($i=0;$i<$length;$i++) {
		$index = rand(0, $length-1);
		list($arr[$i], $arr[$index]) = array($arr[$index], $arr[$i]);
	}
	return $arr;
}
