<?php
//使用uri的方式连接数据库
try{
	$dsn = 'uri:file:///Users/ice/www/PDO/dsn.txt';
	$username = 'root';
	$passwd = '123456';
	$dbh = new PDO($dsn, $username, $passwd);
	var_dump($dbh);
}catch(PDOException $e){
	echo $e->getMessage();
}