<?php

/**
 * PDO数据库类的封装
 * User: maosilu
 * Date: 2017/11/15
 * Time: 上午10:39
 */
header('Content-type:text/html;charset=utf-8');
class PdoMysql
{
    public static $config = array(); // 设置连接参数
    public static $link = null; //保存连接标识符
    public static $pconnect = false; // 是否开启持久连接
    public static $dbVersion = null; // 保存数据库版本
    public static $connected = false; // 判断是否连接成功
    public static $PDOStatement = null; // 保存PDOStatement对象
    public static $queryStr = null; // 保存最后执行的操作

    /**
     * 保存PDO的连接
     * @param string $dbConfig
     * @return boolean
    */
    public function __construct($dbConfig=''){
        if(!class_exists('PDO')){
            self::throw_exception('不支持PDO，请先开启！');
        }
        if(!is_array($dbConfig)){
            $dbConfig = array(
                'hostname' => DB_HOST,
                'username' => DB_USER,
                'password' => DB_PWD,
                'database' => DB_NAME,
                'hostport' => DB_PORT,
                'dbms'     => DB_TYPE,
                'dsn'      => DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME,
            );
        }

        if(empty($dbConfig['hostname'])) self::throw_exception('没有定义数据库配置，请先定义！');
        self::$config = $dbConfig;
        if(empty(self::$config['params'])) self::$config['params'] = array();
        if(!isset(self::$link)){
            $configs = self::$config;
            if(self::$pconnect){
                // 开启长连接，添加到配置数组中
                $configs['params'][constant('PDO::ATTR_PERSISTENT')] = true;
            }

            try{
               self::$link = new PDO($configs['dsn'], $configs['username'], $configs['password'], $configs['params']);
            }catch(PDOException $e){
                self::throw_exception($e->getMessage());
            }

            if(!self::$link){
                self::throw_exception('PDO连接错误！');
                return false;
            }
            self::$link->exec('SET NAMES '.DB_CHARSET);
            self::$dbVersion = self::$link->getAttribute(constant('PDO::ATTR_SERVER_VERSION'));
            self::$connected = true;

            unset($configs);
        }
    }

    /**
     * 得到所有记录
     * */
    public static function getAll($sql = null){
        if($sql != null){
            self::query($sql);
        }
        $result = self::$PDOStatement->fetchAll(constant("PDO::FETCH_ASSOC"));
        return $result;
    }

    /**
     * 释放结果集
     * */
    public static function free(){
        self::$PDOStatement = null;
    }
    public static function query($sql = ''){
        $link = self::$link;
        if(!$link) return false;
        //判断之前是否有结果集，如果有的话，释放结果集
        if(!empty(self::$PDOStatement)) self::free();
        // 保存最后使用的sql语句
        self::$queryStr = $sql;
        self::$PDOStatement = $link->prepare(self::$queryStr);
        $res = self::$PDOStatement->execute();
        self::haveErrorThrowException();
        return $res;
    }

    public static function haveErrorThrowException(){
        $obj = empty(self::$PDOStatement) ? self::$link : self::$PDOStatement;
        $arrError = $obj->errorInfo();
        var_dump($arrError);
    }

    /**
     * 自定义错误处理
     * @param string $errMsg 错误信息
    */
    public static function throw_exception($errMsg){
        echo '<div style="width:80%;background-color:#ABCDEF;color:black;font-size:20px;padding: 20px 0px;">
'.$errMsg.'
</div>';
    }

}

require_once 'config.php';
$PdoMysql = new PdoMysql();
//var_dump($PdoMysql);
$sql = "SELECT * FROM pdo_user1";
var_dump($PdoMysql->getAll($sql));

