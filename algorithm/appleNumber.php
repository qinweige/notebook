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
	
				