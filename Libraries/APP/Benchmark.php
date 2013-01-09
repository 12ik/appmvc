<?php
class APP_Benchmark
{
	private $marker = array();

	public function mark($name) {
		$this->marker[$name] = microtime();
	}

	public function elapsed_time($point1 = '', $point2 = '', $decimals = 6)
	{
		if ($point1 == '')
		{
			$t1 =  current($this->marker);
			$t2 =  microtime();
			list($sm, $ss) = explode(' ', $t1);
			list($em, $es) = explode(' ', $t2);
			return number_format(($em + $es) - ($sm + $ss), $decimals);
		}

		if (!isset($this->marker[$point1]))
		{
			return '';
		}

		if (!isset($this->marker[$point2]))
		{
			$this->marker[$point2] = microtime();
		}
	
		list($sm, $ss) = explode(' ', $this->marker[$point1]);
		list($em, $es) = explode(' ', $this->marker[$point2]);
		return number_format(($em + $es) - ($sm + $ss), $decimals);
	}
	
	public function memory_usage()
	{
		return array(
			'total'=> memory_get_usage(true),
			'peak' => memory_get_peak_usage(true)
		);
	}
}



/*
$bm = new APP_Benchmark;
$bm->mark('t1');
sleep(1);
$bm->mark('t2');
$bm->mark('t3');
$bm->mark('t4');


// 用法一： 对两个时间点做减法
echo $bm->elapsed_time('t1', 't4')   ."\n";     // 1.013979

// 用法二： 及结算全部的时间
echo $bm->elapsed_time();                       // 1.014184

// 说明： 返回值是秒,默认带有6位小数


print_r($bm->memory_usage());

*/
