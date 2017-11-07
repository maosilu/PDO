<?php
header('Content-type:text/html;charset=utf-8');
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$sql = "SELECT username,password,email FROM pdo_user";
	$stmt = $dbh->query($sql);
	echo $stmt->fetchColumn(0)."<br/>";
	echo $stmt->fetchColumn(1)."<br/>";
}catch(PDOException $e){
	echo $e->getMessage();
}