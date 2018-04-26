<?php

$arr = [-8,-7,-6,-4,-2,0,1,2,3,4,5,6,7];
print_r(sum3($arr));

function sum3($arr)
{
	$result = [];
	for ($i=0;$i<count($arr)-1;$i++) {
		//左边的从index之后开始就可以，会覆盖前面的
		$left = $i + 1;
		$right = count($arr) -1;
		//有这个right>left，所以不用担心i,left,right相等
		while ($right > $left) {
			$sum = $arr[$left] + $arr[$i] + $arr[$right];
			if ($sum < 0) {
				$left++;
			} else if ($sum > 0) {
				$right--;
			} else {
				$set = array($arr[$left], $arr[$i], $arr[$right]);
				$result[] = $set;
				//不要忘记++，否则死循环
				$left++;
				$right--;
			}
		}
	}
	return $result;
}