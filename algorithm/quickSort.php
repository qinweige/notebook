<?php

function quickSort($arr)
{
	$length = count($arr);
	//判断结束的标准，单一变量
	if ($length <= 1) return $arr;
	$index = rand(0, $length - 1);
	$reference = $arr[$index];
	$left = $right = array();
	//注意是小于length即可
	for ($i=0;$i<$length;$i++) {
		if ($i!=$index) {
			if ($arr[$i] <= $reference) {
				array_push($left, $arr[$i]); 
			} else {
				array_push($right, $arr[$i]);
			}
		}
	}
	$left = quickSort($left);
	$right = quickSort($right);
	return array_merge($left, [$reference], $right);
}

//testing
$arr = [1];
print_r(quickSort($arr));
$arr = [1,2];
print_r(quickSort($arr));
$arr = [2,1,3];
print_r(quickSort($arr));
$arr = [8,7,6,5,4,0, -1, -3,3,2,1];
print_r(quickSort($arr));
