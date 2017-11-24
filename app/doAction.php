<?php
/**
 * Created by PhpStorm.
 * User: maosilu
 * Date: 2017/11/22
 * Time: 下午4:40
 */
//1.包含所需文件
require_once './swiftmailer-master/lib/swift_required.php';
require_once './PdoMysql.class.php';
require_once './config.php';
//2.接受信息
$act = $_GET['act'];
$username = addslashes($_POST['username']);
$password = md5($_POST['password']);
$emial = $_POST['emial'];
//3.得到连接对象
$PdoMysql = new PdoMysql();
$table = 'user';
if($act === 'reg'){
    //完成注册的功能
    $regtime = time();
    $token = md5($username,$password,$regtime);
    $token_exptime = $regtime+24*3600; // 过期时间
    $data = compact($username, $password, $email, $token, $token_exptime, $regtime);
    $res = $PdoMysql->add($data, $table);
    if($res){
        
    }else{
        echo '用户注册失败，3秒钟后跳转到注册页面';
        echo "<meta http-equiv='refresh' content='3;url=index.php#toregister'/>";
    }
}elseif($act === 'login'){
    //完成登录的功能
}

