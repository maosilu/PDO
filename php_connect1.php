<?php
//通过参数形式连接数据库
try{
	$dsn = 'mysqli:host=localhost;dbname=test';
	$username = 'root';
	$passwd = '123456';
	$dbh = new PDO($dsn, $username, $passwd);
}catch(PDOException $e){
	echo $e->getMessage();
}