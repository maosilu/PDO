<?php
header('Content-type:text/html;charset=utf-8');
$username = $_POST['username'];
$password = $_POST['password'];
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$sql = "SELECT * FROM pdo_user WHERE username=? AND password=?";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array($username, $password));
	echo $stmt->rowCount();
}catch(PDOException $e){
	echo $e->getMessage();
}