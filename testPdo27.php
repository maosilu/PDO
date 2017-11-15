<?php
//1.通过PDO连接数据库
$pStartTime = microtime(true);
for($i=1; $i<=100; $i++){
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
}
$pEndTime = microtime(true);
$res1 = $pEndTime-$pStartTime;

//2.通过mysql连接数据库
$mStartTime = microtime(true);
for($i=1; $i<=100; $i++){
	$link = mysqli_connect('localhost', 'root', '123456');
	mysqli_select_db($link, 'test');
}
$mEndTime = microtime(true);
$res2 = $mEndTime-$mStartTime;

echo $res1."<br/>".$res2;
echo "<hr/>";

if($res1 >= $res2){
	echo 'PDO连接数据库是mysql的'.round($res1/$res2).'倍';
}else{
	echo 'mysql连接数据库是PDO的'.round($res2/$res1).'倍';
}