记录所有算法相关知识。写算法时，可以先写test case，这样比较容易把握和思考。
以下算法都可以直接跑。
1.冒泡算法
```php
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
```
2.快速排序。简单思路，随机挑选一个数组中的值，以它为标准，小于他的放在她的左边，大于他的放在他的右边。之后递归，最后所有的值都会排列好。
```php
<?php

$arr = [8,7,6,5,4,0, -1, -3,3,2,1];
print_r(quickSort($arr));

function quickSort($arr)
{
	$length = count($arr);
	//判断结束的标准，单一变量
	if ($length <= 1) return $arr;
    //取random整数，随机防止特殊情况时间复杂度升为n^2
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
```
3.从两个数组中找出相同的数
```php
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
```
4.重写shuffle函数，打乱一个数组
```php
<?php

$arr = [2,3,4,5,6,7,8,9];
print_r(shuffleRewrite($arr));

//这边不能用shuffle，因为是内置函数。
function shuffleRewrite($arr)
{
	$length = count($arr);
	for($i=0;$i<$length;$i++) {
		$index = rand(0, $length-1);
		list($arr[$i], $arr[$index]) = array($arr[$index], $arr[$i]);
	}
	return $arr;
}
```
5.分离字母和数字。一个string中有字母数字，分离并用：分割
```php
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
```php
6.约瑟夫环问题
相关题目：一群猴子排成一圈，按1,2,…,n依次编号。然后从第1只开始数，数到第m只,把它踢出圈，从它后面再开始数， 再数到第m只，在把它踢出去…，如此不停的进行下去， 直到最后只剩下一只猴子为止，那只猴子就叫做大王。要求编程模拟此过程，输入m、n, 输出最后那个大王的编号。
```
<?php 

$m = 3; $n = 3;
echo roundCount($m, $n);

function roundCount($m, $n)
{
	$roundTable = range(1, $m);
	while (count($roundTable) > 1) {
		for ($i=0;$i<$n-1;$i++) {
			$pop = array_shift($roundTable);
			array_push($roundTable, $pop);
		}
		array_shift($roundTable);
	}
	return $roundTable[0];
}
```
7.二分法
```php
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
```
8.找出一组数中连续的最大和
```php
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
```
9.3sum. 排序好的数列，复杂度n^2。如果没有排列好，可以先用sort
```php
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
```


