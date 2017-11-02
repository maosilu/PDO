<?php
//通过参数形式连接数据库
try{
	$dsn = 'mysql:host=localhost;dbname=test';
	$username = 'root';
	$passwd = '123456';
	$dbh = new PDO($dsn, $username, $passwd);
	var_dump($dbh);
}catch(PDOException $e){
	echo $e->getMessage();
}