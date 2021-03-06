<?php
/**
 * Created by PhpStorm.
 * User: maosilu
 * Date: 2017/11/22
 * Time: 下午4:40
 */
header('content-type:text/html;charset=utf-8');
//1.包含所需文件
require_once './swiftmailer-master/lib/swift_required.php';
require_once './PdoMysql.class.php';
require_once './config.php';
require_once './pwd.php';
//2.接受信息
$act = $_GET['act'];
$username = addslashes($_POST['username']);
$password = md5($_POST['password']);
//3.得到连接对象
$PdoMysql = new PdoMysql();
$table = 'user';
if($act === 'reg'){
    //完成注册的功能
    $email = $_POST['email'];
    $regtime = time();
    $token = md5($username.$password.$regtime);
    $token_exptime = $regtime+24*3600; // 过期时间
    $data = compact('username', 'password', 'email', 'token', 'token_exptime', 'regtime');
    $res = $PdoMysql->add($data, $table);
    $lastInsertId = $PdoMysql->getLastInsertId();
    if($res){
        //发送邮件，以QQ邮箱为例
        //设置邮件服务器，得到传输对象
        $transport = Swift_SmtpTransport::newInstance('smtp.qq.com', 465, 'ssl');
        //设置登录账号和密码
        $transport->setUsername('1540688711@qq.com');
        $transport->setPassword($emailPassword);
        //得到发送邮件对象Swift_Mailer
        $mailer = Swift_Mailer::newInstance($transport);
        //得到邮件信息对象
        $message = Swift_Message::newInstance();
        //设置管理员信息
        $message->setFrom(array('1540688711@qq.com' =>'bingxiaoxiao'));
        //邮件接收方
        $message->setTo(array($email=>'maosilu'));
        //设置邮件主题
        $message->setSubject('激活邮件');
        //设置邮件内容
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?act=active&token={$token}";
        $urlencode = urlencode($url);
        $str = <<<EOF
        亲爱的{$username}您好～！欢迎您注册我们网站。<br/>
        请点击此连接激活账号即可登录！<br/>
        <a href={$url}>{$urlencode}</a>
        <br/>
        如果点击此链接无反应，可以将其复制到浏览器中来执行，链接的有效时间为24小时。
EOF;
        $message->setBody("{$str}", 'text/html', 'utf-8');
        try{
            if($mailer->send($message)){
                echo "恭喜您{$username}注册成功，请到邮箱激活之后登录<br/>";
                echo '3秒后跳转到登录页面';
                echo "<meta http-equiv='refresh' content='3;url=index.php#tologin'/>";
            }else{
                $PdoMysql->delete($table, 'id='.$lastInsertId);
                echo '注册失败，请重新注册<br/>';
                echo '3秒后跳转到注册页面';
                echo '<meta http-equiv="Refresh" content="3;url=index.php#toregister">';
            }
        }catch(Swift_ConnectionException $e){
            echo '邮件发送错误'.$e->getMessage();
        }
    }else{
        echo '用户注册失败，3秒钟后跳转到注册页面';
        echo "<meta http-equiv='refresh' content='3;url=index.php#toregister'/>";
    }
}elseif($act === 'login'){
    //完成登录的功能
    $row = $PdoMysql->find($table, "username='{$username}' AND password='{$password}'", 'status');
    if($row['status'] == 0){
        echo '请先激活，再登录';
//        echo "<meta http-equiv='Refresh' content='3;url=index.php#tologin'/>";
    }else{
        echo '登录成功，3秒后跳转到首页';
        echo "<meta http-equiv='Refresh' content='3;url=https://www.imooc.com'/>";
    }
}elseif($act === 'active'){
    //完成激活操作
    $token = $_GET['token'];
    $row = $PdoMysql->find($table, "token='{$token}' AND status=0", array('id', 'token_exptime'));
    $lastSql = $PdoMysql->getLastSql();
    $now = time();
    if($now > $row['token_exptime']){
        echo '激活时间过期，请重新登录激活';
    }else{
        $res = $PdoMysql->update(array('status'=>1), $table, 'id='.$row['id']);
        if($res){
            echo '激活成功，3秒后跳转到登录页面';
            echo "<meta http-equiv='Refresh' content='3;url=index.php#tologin'/>";
        }else{
            echo '激活失败，请重新登录激活';
            echo "<meta http-equiv='Refresh' content='3;url=index.php'/>";
        }
    }
}

