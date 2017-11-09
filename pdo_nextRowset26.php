<?php
header('Content-type:text/html;charset=utf-8');
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$sql = "CALL test1()";
	$stmt = $dbh->query($sql);

	echo "<pre>";
	$rowset = $stmt->fetchAll(PDO::FETCH_ASSOC);
	var_dump($rowset);

	echo "<hr/>";
	$stmt->nextRowset();
	$rowset = $stmt->fetchAll(PDO::FETCH_ASSOC);
	var_dump($rowset);
}catch(PDOException $e){
	echo $e->getMessage();
}