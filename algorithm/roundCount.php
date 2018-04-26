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
			