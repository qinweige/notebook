记录所有算法相关知识。算法均已验证，可以直接运行。
如有错误，谢谢指出。

1.斐波那契数列两种实现方式
```php
<?php

print_r(fibonacci(50));

function fibonacciRecursive($m)
{
	if ($m <=1) {
		return $m;
	}
	//每次都会再生成两个新的fibonacci，时间复杂度2^n.不建议用这种。对于$m=100.程序崩溃
	return fibonacciRecursive($m-1) + fibonacciRecursive($m-2);
}

function fibonacci($m)
{
	$fibonacciArray[0] = 0;
	$fibonacciArray[1] = 1;
	for ($i=2;$i<$m;$i++) {
		$fibonacciArray[$i] = $fibonacciArray[$i-1] + $fibonacciArray[$i-2];
	}
	return $fibonacciArray;
}
```
2.有5个人偷了一堆苹果，准备在第二天分赃。 晚上，有一人遛出来，把所有苹果分成5份，但是多了一个，顺手把这个扔给树上的猴了，自己先拿1/5藏了。没想到其他四人也都是这么想的，都如第一个人一样分成5份把多的那一个扔给了猴，偷走了1/5。第二天，大家分赃，也是分成5份多一个扔给猴了。最后一人分了一份。问：共有多少苹果？N 个人呢？
```php
<?php
$peopleNo = 5;
echo totalApple($peopleNo);

function totalApple($peopleNo)
{
	for ($i=1; ;$i++) {
		$result = $i;
		for ($m=0;$m<=$peopleNo;$m++) {
			if ($result%$peopleNo == 1) {
				$result = $result - round(($result-1)/$peopleNo) - 1;
			} else {
				continue 2;
			}
		}
		return $i;
	}
}
```
3.输出一组数组的全排列。 [1,2,3] 输出 [1,2,3] [1,3,2] [2,1,3] [2,3,1] [3,1,2] [3,2,1]
```php
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
```
4.假设密码为六位数字构成，比如‘000231’ ‘345678’。但我们不知道传入的密码值，要求破解输出密码。	
```php
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
```
5.看到过一篇数组一一对应的加密解密算法，又想到array_map这种可以传递callback的函数。所以试着重写加密解密函数.相似的函数还有array_filter, array_walk.这篇文章对array函数总结的很好
https://code.tutsplus.com/tutorials/working-with-php-arrays-in-the-right-way--cms-28606
```php
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
```