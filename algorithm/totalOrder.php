<?php 

$arr = [1,2,3];
print_r(totalOrder($arr, 0, count($arr)-1));

//递归实现。index是当前要交换的位置，end是最后的位置
function totalOrder($arr, $index, $end)
{
	//递归最重要的是输出或者结束条件，当index到达最后，说明所有值以交换一遍
	if ($index == $end) {
		print_r($arr);
	}
	for ($i=$index;$i<=$end;$i++) {
		//index位置和i位置交换
		list($arr[$i], $arr[$index]) = array($arr[$index], $arr[$i]);
		totalOrder($arr, $index+1, $end);
		//换回，不影响其他递归的继续
		list($arr[$i], $arr[$index]) = array($arr[$index], $arr[$i]);
	}
}
	