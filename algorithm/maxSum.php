<?php

$arr = [-4, -2, 4, -3, 6, -2, 9, -3];
echo maxSum($arr);

function maxSum($arr)
{
    $currentSum = 0;
    $maxSum = 0;//数组元素全为负的情况，返回最大数

    for ($i = 0; $i < count($arr); $i++) { 
        if ($currentSum >= 0) {
			if ($maxSum < $currentSum) 
				$maxSum = $currentSum;
            $currentSum += $arr[$i];
        } else {
            $currentSum = $arr[$i];
        }
    }
    return $maxSum;
}

