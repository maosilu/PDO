<?php
/**
比较PDO预处理插入数据与mysqli插入数据的效率
*/

header('content-type:text/html;charset=utf-8');
//1.通过PDO连接数据库
$pStartTime = microtime(true);
$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
$sql = "INSERT test2 VALUE(:id)";
$stmt = $dbh->prepare($sql);
for($i=1; $i<=500; $i++){
	$id = 1;
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
}
$pEndTime = microtime(true);
$res1 = $pEndTime-$pStartTime;
unset($dbh);

//2.通过mysql连接数据库
$mStartTime = microtime(true);
$link = mysqli_connect('localhost', 'root', '123456');
mysqli_select_db($link, 'test');
for($i=1; $i<=500; $i++){
	$sql = "INSERT test2 VALUE(2)";
	mysqli_query($link, $sql);
}
mysqli_close($link);
$mEndTime = microtime(true);
$res2 = $mEndTime-$mStartTime;

echo $res1."<br/>".$res2;
echo "<hr/>";

if($res1 >= $res2){
	echo 'PDO插入500条记录是mysql的'.round($res1/$res2).'倍';
}else{
	echo 'mysql插入500条记录是PDO的'.round($res2/$res1).'倍';
}