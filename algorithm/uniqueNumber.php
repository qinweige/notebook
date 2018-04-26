<?php

$arr1 = [5,4,3,2,1];
$arr2 = [4,5,6,7,8];
print_r(uniqueNumber($arr1, $arr2));

function uniqueNumber($arr1, $arr2)
{
	sort($arr1);
	sort($arr2);
	$length1 = count($arr1);
	$length2 = count($arr2);
	$index1 = $index2 = 0;
	$container = [];
	while ($index1 < $length1 && $index2 < $length2) {
		if($arr1[$index1] < $arr2[$index2]) {
			$index1++;
		} else if ($arr1[$index1] > $arr2[$index2]) {
			$index2++;
		} else {
			$container[] = $arr1[$index1];
			//放入container之后，不要忘记增加index
			$index1++;
			$index2++;
		}
	}
	return array_unique($container);
}
			

