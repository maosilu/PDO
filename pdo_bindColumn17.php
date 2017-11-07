<?php
header('Content-type:text/html;charset=utf-8');
try{
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	$sql = "SELECT username,password,email FROM pdo_user";
	$stmt = $dbh->prepare($sql);
	$stmt->execute();

	echo '结果集中的列数：'.$stmt->columnCount()."<br/>";

	$meta = $stmt->getColumnMeta(0);
	echo '结果集中一列的元数据：';
	var_dump("<pre>", $meta);
	
	$stmt->bindColumn(1, $username);
	$stmt->bindColumn(2, $password);
	$stmt->bindColumn('email', $email);
	while($row = $stmt->fetch(PDO::FETCH_BOUND)){
		echo '用户名：'.$username."\t".$password."\t".$email."<br/>";
	}
}catch(PDOException $e){
	echo $e->getMessage();
}