<?php

$password = "555555";
echo hackPassword($password);

function hackPassword($password)
{
	//去掉前面的0，之后才能转换成整数
	$password = ltrim($password, '0');
	$password = intval($password);
	$left = 0;
	$right = 999999;
	//相当于二分法，但是最后返回不同
	while (true) {
		$middle = intval(($left + $right)/2);
		if ($middle > $password) {
			$right = $middle - 1;
		} else if ($middle < $password) {
			$left = $middle + 1;
		} else {
			$hack = $middle;
			break;
		}
	}
	//前边放0组成六位密码
	$hack = str_pad($hack, 6, '0', STR_PAD_LEFT);
	return $hack;
}