<?php
header('Content-type:text/html;charset=utf-8');
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456', array(PDO::ATTR_AUTOCOMMIT=>0, PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
	echo '自动提交：'.$dbh->getAttribute(PDO::ATTR_AUTOCOMMIT);
	echo "<br/>";
	echo '错误模式：'.$dbh->getAttribute(PDO::ATTR_ERRMODE);
}catch(PDOException $e){
	echo $e->getMessage();
}