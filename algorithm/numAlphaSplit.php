<?php

$numAlpha = "abc123dfg4jkl567ddd";
print_r(numAlphaSplit($numAlpha));

function numAlphaSplit($numAlpha)
{
	$number = preg_split('/[a-z]+/', $numAlpha, -1, PREG_SPLIT_NO_EMPTY);
    $alpha = preg_split('/\d+/', $numAlpha, -1, PREG_SPLIT_NO_EMPTY);
	$result = '';
	for($i=0;$i<count($number);$i++) {
		$result .= $number[$i] . ':' . $alpha[$i];
	}
	//不要忘了其中一个可能会多，有没有更好的方法？
	$result .= isset($alpha[count($number)])?$alpha[count($number)]:'';
	return $result;
}