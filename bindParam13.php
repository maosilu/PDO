<?php
header('Content-type:text/html;charset=utf-8');
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$sql = "INSERT INTO pdo_user(username, password, email) VALUES(?, ?, ?)";
	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(1, $username, PDO::PARAM_STR);
	$stmt->bindParam(2, $password, PDO::PARAM_STR);
	$stmt->bindParam(3, $email);
	$username = 'test';
	$password = 'test';
	$email = 'test@imooc.com';
	$stmt->execute();
	echo $stmt->rowCount();
}catch(PDOException $e){
	echo $e->getMessage();
}