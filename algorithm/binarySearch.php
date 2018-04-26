<?php

$arr = [1,3,5,7,9,11,13];
echo binarySearch($arr, 11);

function binarySearch($arr, $index)
{
	$length = count($arr);
	$left = 0;
	$right = $length-1;
	
	while ($left <= $right) {
		//注意要在while里边求mid值
		$mid = intval(($left+$right)/2);
		//用arr[$mid]和要找的值比较，用mid来增减位置
		if ($arr[$mid] > $index) {
			$right = $mid - 1;
		} else if ($arr[$mid] < $index) {
			$left = $mid + 1;
		} else {
			return $mid;
		}
	}
	return -1;
}