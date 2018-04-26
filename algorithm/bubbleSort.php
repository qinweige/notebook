<?php

$arr = [2,1,4,3,8,9,7,6,5];
print_r(bubbleSort($arr));

function bubbleSort($arr) 
{
	$length = count($arr);
	for($i=0;$i<$length;$i++) {
		for($j=$i+1;$j<$length;$j++) {
			if ($arr[$i] > $arr[$j]) {
				//这种形式比较简便易读
				list($arr[$i], $arr[$j]) = array($arr[$j], $arr[$i]);
			}
		}
	}
	return $arr;
}