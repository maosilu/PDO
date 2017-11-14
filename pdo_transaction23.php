<?php
header("content-type:text/html;charset=utf-8");
try{
	$options = array(PDO::ATTR_AUTOCOMMIT, 0); //关闭事务的自动提交
	$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '123456');
	var_dump($dbh->inTransaction());
	//开启事务
	$dbh->beginTransaction();
	var_dump($dbh->inTransaction());

	$sql = "UPDATE userAccount SET salary=salary-1000 WHERE username='king'";
	$res1 = $dbh->exec($sql);
	var_dump($res1);
	if($res1 == 0){
		throw new PDOException('king 转账失败！');
	}
	$res2 = $dbh->exec("UPDATE userAccount SET salary=salary+1000 WHERE username='imooc'");
	if($res2 == 0){
		throw new PDOException('imooc 接收失败！');
	}
	$dbh->commit();
}catch(PDOException $e){
	//回滚事务
	$dbh->rollBack();
	echo $e->getMessage();
}
