<?php

$arr = 'ezacbdzfcg';

$mappedArr = array_map("decrypt", str_split($arr));
echo implode('', $mappedArr);

function decrypt($c)
{
	//加密解密数组，一一对应
	$encryption = ['a','b','c','d','e','f','g'];
	$decryption = ['l','v','o','e','I','y','u'];
	$index = array_search($c, $encryption);
	//如果存在即输出，不存在就输出空格。注意要用===，否则index为零时也会判断成false
	if ($index === false) return ' ';
	return $decryption[$index];
}
