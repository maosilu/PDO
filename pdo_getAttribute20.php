<?php
header('Content-type:text/html;charset=utf-8');
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$attrArr = array(
		'AUTOCOMMIT', 'ERRMODE', 'CASE', 'CLIENT_VERSION', 'CONNECTION_STATUS', 'ORACLE_NULLS', 'PERSISTENT', 'PREFETCH', 'SERVER_INFO', 'SERVER_VERSION', 'TIMEOUT'
	);
	foreach($attrArr as $attr){
		echo 'PDO::ATTR_'.$attr.'：';
		echo $dbh->getAttribute(constant("PDO::ATTR_$attr"))."<br/>";
	}
}catch(PDOException $e){
	echo $e->getMessage();
}