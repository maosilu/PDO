<?php
header('Content-type:text/html;charset=utf-8');
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$sql = "SELECT * FROM pdo_user";
	$stmt = $dbh->prepare($sql);
	$res = $stmt->execute();
	echo "<pre>";
	// if($res){
	// 	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
	// 		var_dump($row);
	// 		echo "<hr/>";
	// 	}
	// }

	// $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	// var_dump($rows);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$rows = $stmt->fetchAll();
	var_dump($rows);
}catch(PDOException $e){
	echo $e->getMessage();
}