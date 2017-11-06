<?php
header('Content-type:text/html;charset=utf-8');
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$sql = "INSERT INTO pdo_user(username, password, email) VALUES(:username, :password, :email)";
	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(':username', $username, PDO::PARAM_STR);
	$stmt->bindParam(':password', $password, PDO::PARAM_STR);
	$stmt->bindParam(':email', $email);
	$username = 'imooc';
	$password = 'imooc';
	$email = 'imooc@imooc.com';
	$stmt->execute();
	$username = 'Mr.king';
	$password = 'Mr.king';
	$email = 'Mr.king@imooc.com';
	$stmt->execute();
	echo $stmt->rowCount();
}catch(PDOException $e){
	echo $e->getMessage();
}