<?php
header('Content-type:text/html;charset=utf-8');
$username = $_POST['username'];
$password = $_POST['password'];
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	//通过quote()：返回带引号的字符串，过滤字符串中的特殊字符
	$username = $dbh->quote($username);
	$sql = "SELECT * FROM pdo_user WHERE username={$username} AND password='{$password}'";
	echo $sql;
	echo "<br/>";
	$stmt = $dbh->query($sql);
	//PDOStatement对象的方法：rowCount()对于select操作返回结果集中记录的条数
	//对于INSERT，UPDATE，DELETE返回受影响的记录的条数
	echo $stmt->rowCount();
}catch(PDOException $e){
	echo $e->getMessage();
}