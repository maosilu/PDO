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
require_once './pwd.php';
//2.接受信息
$act = $_GET['act'];
$username = addslashes($_POST['username']);
$password = md5($_POST['password']);
$email = $_POST['email'];
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
        //发送邮件，以QQ邮箱为例
        //设置邮件服务器，得到传输对象
        $transport = Swift_SmtpTransport::newInstance('smtp.qq.com', 25);
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
        $message->setTo(array($emial=>'maosilu'));
        //设置邮件主题
        $message->setSubject('激活邮件');
        //设置邮件内容
        $str = <<<EOF
        亲爱的{$username}您好～！欢迎您注册我们网站。<br/>
            请点击此连接激活账号即可登录！<br/>

EOF;

        $message->setBody("{$str}", 'text/html', 'utf-8');
    }else{
        echo '用户注册失败，3秒钟后跳转到注册页面';
        echo "<meta http-equiv='refresh' content='3;url=index.php#toregister'/>";
    }
}elseif($act === 'login'){
    //完成登录的功能
}

