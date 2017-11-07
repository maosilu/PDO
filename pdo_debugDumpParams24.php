<?php
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$sql = "INSERT INTO pdo_user(username,password,email) VALUES(?,?,?)";
	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(1, $username, PDO::PARAM_STR);
	$stmt->bindParam(2, $password, PDO::PARAM_STR);
	$stmt->bindParam(3, $emial, PDO::PARAM_STR);
	$username='testParam';
	$password = 'testParam';
	$email = 'testParam@imooc.com';
	$stmt->execute();
	echo "<pre>";
	$stmt->debugDumpParams();
}catch(PDOException $e){
	echo $e->getMessage();
}